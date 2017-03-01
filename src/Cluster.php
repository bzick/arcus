<?php

namespace Arcus;


/**
 *
 * @package Arcus
 */
class Cluster {
    /**
     * @var string
     */
    private $_name;
    /**
     * @var RedisHub
     */
    private $_bus;
    /**
     * @var ChannelFactoryInterface
     */
    private $_queue;

    /**
     * Cluster constructor.
     *
     * @param string $name cluster name
     * @param RedisHub $bus
     * @param ChannelFactoryInterface $queue
     */
    public function __construct(string $name, RedisHub $bus, ChannelFactoryInterface $queue) {
        $this->_name = $name;
        $this->_bus = $bus;
        $this->_queue = $queue;
    }

    /**
     * @param string $name daemon's name
     *
     * @return Daemon
     */
    public function addDaemon(string $name = "") {
        return new Daemon($this, $name);
    }

    public function getRedisHub() : RedisHub {
        return $this->_bus;
    }

    public function getQueueHub() : ChannelFactoryInterface {
        return $this->_queue;
    }
}