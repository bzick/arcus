<?php

namespace Arcus\Redis\QueueHub;

use Arcus\Channel\ProducerAbstract;
use Arcus\TaskAbstract;

class Producer extends ProducerAbstract {
    /**
     * @var \Redis
     */
    protected $_redis;

    public function __construct(\Redis $redis, string $producer_name) {
        $this->_producer = $producer_name;
        $this->_redis = $redis;
    }

    public function hasConsumers() : bool {
        return $this->_redis->exists($this->_producer."#consumers");
    }

    public function push(TaskAbstract $task) : bool {
        if($this->_redis->lLen($this->_producer) > $this->_count) {
            return false;
        } else {
            $this->_redis->lPush($this->_producer, serialize($task));
            return true;
        }
    }

    public function getCountConsumers() : int {
        return $this->_redis->sCard($this->_producer."#consumers");
    }

    public function getConsumersNames() : array {
        return $this->_redis->sMembers($this->_producer."#consumers");
    }

    public function getCountTasks() : int {
        return $this->_redis->lLen($this->_producer);
    }
}