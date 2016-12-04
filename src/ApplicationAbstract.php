<?php

namespace Arcus;


use Arcus\Application\InternalQueue;
use Arcus\Daemon\Worker;
use ION\Promise;
use Psr\Log\LogLevel;

abstract class ApplicationAbstract implements EntityInterface {

    /**
     * @var string
     */
    protected $_name;
    /**
     * @var QueueHubInterface
     */
    protected $_worker;
    /**
     * @var InternalQueue
     */
    protected $_internal;

    public function getName() : string {
        return $this->_name;
    }

    public function __toString() {
        return get_called_class()."({$this->_name})";
    }

    /**
     * @return bool
     */
    abstract public function start() : bool;
    abstract public function stop() : bool;

    public function enable(Worker $worker) : bool {
        $this->_worker = $worker;
        $this->_internal = new InternalQueue($worker->getQueueHub(), $this);
        return $this->start();
    }

    public function getWorker() {

    }

    public function disable() : Promise {
        return \ION::promise(true);
    }

    public function halt() {

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

    public function fatal(\Throwable $error) {
        // TODO: Implement fatal() method.
    }

    abstract public function dispatch(TaskAbstract $task);
}