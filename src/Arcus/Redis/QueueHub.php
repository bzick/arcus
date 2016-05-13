<?php

namespace Arcus\Redis;


use Arcus\QueueHub\ConsumerInterface;
use Arcus\QueueHub\ProducerInterface;
use Arcus\QueueHubInterface;
use Arcus\Redis\QueueHub\Consumer;
use Arcus\Redis\QueueHub\Producer;

class QueueHub implements QueueHubInterface {

    /**
     * @var \Redis
     */
    private $_redis;

    public function __construct(\Redis $redis) {
        $this->_redis = $redis;
    }

    public function getRedis() {
        return $this->_redis;
    }

    public function getProducer(string $name) : ProducerInterface {
        return new Producer($this->_redis, $name);
    }

    public function getAppConsumers(string $name) : array {
        // TODO: Implement getAppConsumers() method.
    }

    public function getAppProducer(string $name, string $type = 'cluster') : array {
        // TODO: Implement getAppProducer() method.
    }

    public function getConsumer(string $producer, string $consumer) : ConsumerInterface {
        return new Consumer($this->_redis, $producer, $consumer);
    }
}