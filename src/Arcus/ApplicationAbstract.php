<?php

namespace Arcus;


use Psr\Log\LogLevel;

class ApplicationAbstract implements EntityInterface {

    protected $_name;

    public function getName() {
        return $this->_name;
    }

    public function __toString() {
        return get_called_class()."({$this->_name})";
    }

    public function setUp(QueueHubInterface $queue) {
        $this->queue = $queue;
        $this->_own_consumer = $queue->getConsumer(Daemon::current()->worker()->getName()."/".$this->_name);
        $this->_daemon_consumer = $queue->getConsumer(Daemon::current()->getName()."/".$this->_name);
        $this->_cluster_consumer = $queue->getConsumer(Daemon::current()->getCluster()->getName()."/".$this->_name);
    }

    public function enable() {
        // TODO: Implement enable() method.
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
}