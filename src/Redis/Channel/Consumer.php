<?php

namespace Arcus\Redis\Channel;


use Arcus\Channel\ConsumerAbstract;
use Arcus\Channel\ConsumerInterface;
use Arcus\Log;
use ION\Stream;

class Consumer extends ConsumerAbstract {

    /**
     * @var \Redis
     */
    protected $_redis;
    protected $_consumer;
    /**
     * @var Stream[]
     */
    protected $_sockets = [];
    /**
     * @var Stream
     */
//    protected $_socket;
    protected $_listen = false;
    protected $_host;
    protected $_dbn;

    public function __construct(\Redis $redis, string $consumer) {
        $this->_consumer = $consumer;
        $this->_redis    = $redis;

        $this->_dbn  = $redis->database ?? 0;
        $hostname    = $redis->host ?? "127.0.0.1";
        $port        = $redis->port ?? 6379;
        $this->_host = $hostname.":".$port;
    }

    public function getCountReservedTasks() : int {
        return $this->_redis->lLen($this->_consumer."#reserved");
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
        $tasks = $this->_redis->lRange($this->_consumer."#reserverd", 0, -1);
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
        if(!$this->_sockets) {
            /* redis don't have MBRPOPLPUSH yet (see https://github.com/antirez/redis/issues/1785) */
            foreach($this->_channels as $channel) {
                $socket = Stream::socket($this->_host);
                $socket->channel = $channel;
                $socket->incoming()->then([$this, "_onTask"])->then($this->_on_task, $this->_on_problem);
                $this->_redis->sAdd($channel . "#consumers", $this->_consumer);
                if($this->_dbn) {
                    $socket->write("SELECT {$this->_dbn}\r\n");
                }
                $socket->enable();
                $socket->write("BRPOPLPUSH ".$channel." {$this->_consumer}#reserved 0\r\n");
                $this->_sockets[ $channel ] = $socket;
            }
        }
        return $this;
    }

    public function release() : self {
        $this->_redis->del("{$this->_consumer}#reserverd");
        return $this;
    }

    public function disable() : \Generator {
        if($this->_listen) {
            $this->_listen = false;
        }
        foreach ($this->_sockets as $channel => $socket) {
            if($socket->getSize()) {
                $this->_onTask($socket);
            }
            $this->_redis->sRem($channel . "#consumers", $this->_consumer);
            yield $socket->shutdown()->closed();
            unset($this->_sockets[$channel]);
        }
        return $this;
    }

    public function close() : self {
        $this->disable();
        return $this;
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
                                if ($value = $this->_redis->rpoplpush($this->_channels, "{$this->_channels}#consumers:{$this->_consumer}")) {
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
        $count = 0;
        foreach ($this->_channels as $channel) {
            $count += $this->_redis->lLen($channel);
        }
        return $count;
    }

    /**
     * Подписан ли потребитель на этот канал
     *
     * @param string $channel
     *
     * @return bool
     */
    public function hasChannel(string $channel) : bool
    {
        return in_array($channel, $this->_channels);
    }

}