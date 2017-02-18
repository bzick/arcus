<?php

namespace Arcus;


use Arcus\Application\InternalQueue;
use Arcus\Daemon\WorkerDispatcher;
use ION\Promise;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

abstract class ApplicationAbstract implements ApplicationInterface {

    /**
     * @var string
     */
    protected $_name = 'noname';
    /**
     * @var ChannelFactoryInterface
     */
    protected $_worker;
    /**
     * @var InternalQueue
     */
    protected $_internal;

    /**
     * @var LoggerInterface
     */
    protected $_logger;
    protected $_current_task;

    public function getName() : string
    {
        return $this->_name;
    }

    public function getURI(string $of = 'self')
    {

    }

    public function __toString()
    {
        return get_called_class()."({$this->_name})";
    }

    public function enable(WorkerDispatcher $worker) : bool {
        $this->_worker = $worker;
        $this->_internal = new InternalQueue($worker->getQueueHub(), $this);
        return true;
    }

    public function getWorker() {

    }

    public function disable() : Promise {
        return \ION::promise(true);
    }

    public function halt() {

    }

    abstract public function inspect() : array;

    public function setLogger(LoggerInterface $logger) {
        $this->_logger = $logger;
    }

    public function getLogger() : LoggerInterface {

    }

    public function log($message, $level = LogLevel::DEBUG) {
        if($this->_logger) {
            $this->_logger->log($level, $message, ["app" => $this]);
        } elseif(LogLevel::DEBUG !== $level) {
            Log::message($message, $level);
        }
    }

    public function logRotate() {
        // TODO: Implement logRotate() method.
    }

    public function fatal(\Throwable $error) {
        // TODO: Implement fatal() method.
    }

    abstract public function dispatch(TaskAbstract $task);

    public function resume(TaskAbstract $task) : self {
        $this->_current_task = $task;
    }

    public function suspend() {
        $this->_current_task = null;
    }

    public function hasCurrentTask() {
        return (bool)$this->_current_task;
    }

    public function getCurrentTask() {
        return $this->_current_task;
    }
}