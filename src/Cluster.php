<?php

namespace Arcus;


use ION\Process;

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
     * @var QueueHubInterface
     */
    private $_queue;

    /**
     * Cluster constructor.
     *
     * @param string $name cluster name
     * @param RedisHub $bus
     * @param QueueHubInterface $queue
     */
    public function __construct(string $name, RedisHub $bus, QueueHubInterface $queue) {
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

    public function getQueueHub() : QueueHubInterface {
        return $this->_queue;
    }
}