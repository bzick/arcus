<?php

namespace Arcus;


use Arcus\QueueHub\ClusterConsumer;
use Psr\Log\LogLevel;

abstract class ApplicationAbstract implements EntityInterface {

    /**
     * @var string
     */
    protected $_name;
    /**
     * @var QueueHubInterface
     */
    protected $_queue;
    /**
     * @var ClusterConsumer
     */
    protected $_consumer;

    public function getName() : string {
        return $this->_name;
    }

    public function __toString() {
        return get_called_class()."({$this->_name})";
    }

    abstract public function start() : bool;
    abstract public function stop() : bool;

    public function enable(QueueHubInterface $queue) : bool {
        $this->_queue = $queue;
        $this->_consumer = new ClusterConsumer($this->_queue, $this);
        return $this->start();
    }

    public function disable() {
        // TODO: Implement disable() method.
    }

    public function halt() {
        // TODO: Implement halt() method.
    }

    public function inspect() {
        // TODO: Implement inspect() method.
    }

    public function log($message, $level = LogLevel::DEBUG) {
        // TODO: Implement log() method.
    }

    public function logRotate() {
        // TODO: Implement logRotate() method.
    }

    public function fatal(\Exception $error) {
        // TODO: Implement fatal() method.
    }

    abstract public function dispatch(TaskAbstract $task);
}