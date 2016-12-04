<?php

namespace Arcus\Redis\QueueHub;


use Arcus\Log;
use Arcus\QueueHub\ConsumerAbstract;
use ION\Stream;

class Consumer extends ConsumerAbstract {

    /**
     * @var \Redis
     */
    protected $_redis;
    protected $_consumer;
    /**
     * @var Stream
     */
    protected $_socket;
    protected $_listen = false;
    protected $_host;
    protected $_dbn;

    public function __construct(\Redis $redis, string $queue_name, string $consumer) {
        $this->_producer = $queue_name;
        $this->_consumer = $consumer;
        $this->_redis = $redis;

        $this->_dbn  = $redis->database ?? 0;
        $hostname    = $redis->host ?? "127.0.0.1";
        $port        = $redis->port ?? 6379;
        $this->_host = $hostname.":".$port;
    }

    public function setLockName(string $name) {
        $this->_consumer = $name;
    }

    /**
     * Возвращает список зарезервированных под выполенение задач
     * @return array
     */
    /**
     * Возвращает список зарезервированных под выполенение задач
     * @return array
     */
    public function getReservedTasks() : array {
        $tasks = $this->_redis->lRange($this->_producer."#consumers:".$this->_consumer, 0, -1);
        if($tasks) {
            foreach($tasks as &$task) {
                $task = unserialize($task);
            }
            return $tasks;
        } else {
            return [];
        }
    }

    public function enable() : self {
        if(!$this->_socket) {
            $this->_socket = Stream::socket($this->_host);
            $this->_socket->incoming()->then([$this, "_onTask"])->then($this->_on_task);
            $this->_redis->sAdd($this->_producer . "#consumers", $this->_consumer);
            if($this->_dbn) {
                $this->_socket->write("SELECT {$this->_dbn}\r\n");
            }
            $this->_socket->enable();
        }
        if(!$this->_listen) {
            $this->_socket->write("BRPOPLPUSH {$this->_producer} {$this->_producer}#consumers:{$this->_consumer} 0\r\n");
            $this->_listen = true;
        }
        return $this;
    }

    public function release() : self {
        $this->_redis->del("{$this->_producer}#consumers:{$this->_consumer}");
        return $this;
    }

    public function disable() : self {
        if($this->_listen) {
            $this->_listen = false;
        }
        if($this->_socket) {
            $this->_redis->sRem($this->_producer . "#consumers", $this->_consumer);
            if($this->_socket->getSize()) {
                $this->_onTask($this->_socket);
            }
            $this->_socket->shutdown();
            $this->_socket = null;
        }
        return $this;
    }

    public function close() : self {
        $this->disable();
    }

    /**
     * Сообщение от редиса
     * Пример ответа команды BRPOPLPUSH
     *
     * <pre>
     * $12\r\n
     * s:5:"data1";\r\n
     * </pre>
     *
     * @param Stream $stream
     *
     * @return \Generator (yields)
     */
    protected function _onTask(Stream $stream) {
        do {
            $head = yield $stream->readLine("\r\n");
            switch ($head[0]) {
                case "$":
                    $length = (int)substr($head, 1) + 2; // + \r\n
                    $data   = yield $stream->read($length);
                    try {
                        $this->_listen = false;
                        $this->_task($data);
                        if ($this->_batch_size) {
                            for ($i = 0; $i < $this->_batch_size; $i++) {
                                if ($value = $this->_redis->rpoplpush($this->_producer, "{$this->_producer}#consumers:{$this->_consumer}")) {
                                    $this->_task($value);
                                } else {
                                    break;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error($e);
                    } finally {
                        if ($this->_auto_enable) {
                            $this->enable();
                        }
                    }
                    break;
                case "-": // error message
                    Log::error("$this: redis error: " . $head);
                    return;
                case "+": // success on SELECT etc
                    break;
                default:
                    Log::warning("$this: unknown redis response head: $head");
                    return;
            }
        } while($stream->getSize());
    }

    /**
     * @param string $data
     * @throws \Exception
     */
    private function _task($data) {
        try {
            call_user_func($this->_on_task, unserialize($data));
        } finally {
            if($this->_auto_release) {
                $this->release();
            }
        }
    }

    public function getCountTasks() : int {
        return $this->_redis->lLen($this->_producer);
    }
}