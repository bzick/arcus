<?php

namespace Arcus\Redis;


use Arcus\Channel\ConsumerInterface;
use Arcus\Channel\ProducerInterface;
use Arcus\ChannelFactory;
use Arcus\Redis\QueueHub\Consumer;
use Arcus\Redis\QueueHub\Producer;

class QueueHub implements ChannelFactory {

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

    public function getConsumer(string $channel_name, string $consumer) : ConsumerInterface {
        return new Consumer($this->_redis, $channel_name, $consumer);
    }
}