<?php

namespace Arcus\Redis;


use Arcus\Channel\ConsumerInterface;
use Arcus\Channel\ProducerInterface;
use Arcus\ChannelFactoryInterface;
use Arcus\Redis\Channel\Consumer;
use Arcus\Redis\Channel\Producer;
use Arcus\TaskAbstract;

class RedisChannelFactory implements ChannelFactoryInterface {

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


    public function getConsumer(string $consumer) : ConsumerInterface {
        return new Consumer($this->_redis, $consumer);
    }

    public function push(string $channel, TaskAbstract $task): bool
    {
        return (new Producer($this->_redis, $channel))->push($task);
    }
}