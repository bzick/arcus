<?php

namespace Arcus\Daemon;


use Arcus\Daemon;
use Arcus\Daemon\Plant\RegulatorInterface;
use Arcus\ApplicationInterface;
use Arcus\Log;
use Arcus\Daemon\Plant\WorkerProcess;
use ION\Process;

/**
 * Логика кротроля за подпроцессами и их количеством.
 * @package Arcus\Daemon
 */
class Plant {

    public $name;
    private $_user;
    private $_group;
    private $_priority;
    /**
     * @var callable
     */
    private $_regulator;
    private $_work_dir;
    private $_count = 0;
    /**
     * @var ApplicationInterface[]
     */
    private $_apps = [];

    /**
     * Workers' pid
     * @var WorkerProcess[]
     */
    private $_pids = [];

    /**
     * @var WorkerDispatcher
     */
    private $_dispatcher;

    /**
     * Area constructor.
     *
     * @param Daemon $daemon
     * @param string $name
     * @param RegulatorInterface $regulator
     */
    public function __construct(Daemon $daemon, string $name, RegulatorInterface $regulator) {
        $this->daemon = $daemon;
        $this->_regulator = $regulator;
        $this->name = $name;
    }

    public function getDaemon() : Daemon {
        return $this->daemon;
    }

    public function getName() : string {
        return $this->name;
    }

    public function getProcessCount() : int {
        return $this->_count;
    }

    /**
     * Выдает список нагу
     * @return array
     */
    public function getLoad() : array {
        $load = [];
        foreach ($this->_pids as $pid => $worker) {
            if($worker->isAlive()) {
                $load[$pid] = $worker->load;
            }
        }
        return $load;
    }

    /**
     *
     * Выдает среднуюю нагрузку на заводе
     * @return float
     */
    public function getLoadAverage() : float {
        $load = $this->getLoad();
        return array_sum($load)/count($load);
    }

    /**
     * Меняет cwd для процесса
     * @param string $path
     *
     * @return $this
     */
    public function setWorkDir(string $path) {
        $this->_work_dir = realpath($path);
        if($this->_work_dir) {
            throw new \InvalidArgumentException("Directory $path doesn't exists.");
        }
        return $this;
    }

    /**
     * @param string $user_name
     *
     * @return Plant
     */
    public function setUser(string $user_name) : self {
        $user = Process::getUser($user_name);
        if(!$user) {
            throw new \RuntimeException("User $user_name does not exists");
        } else {
            Log::debug("Area {$this->name}: workers will be change user to {$user_name} ({$user['uid']})");
        }
        $this->_user = $user_name;
        return $this;
    }

    /**
     * @param string $group_name
     *
     * @return Plant
     */
    public function setGroup(string $group_name) : self {
        $group = Process::getGroup($group_name);
        if(!$group) {
            throw new \RuntimeException("Group $group_name does not exists");
        } else {
            Log::debug("Area {$this->name}: workers will be change group to {$group_name} ({$group['gid']})");
        }
        $this->_group = $group_name;
        return $this;
    }

    /**
     * @param int $priority
     *
     * @return Plant
     */
    public function setPriority(int $priority) : self {
        $this->_priority = $priority;
        return $this;
    }

    /**
     * Add entity (application or server) to group of workers
     *
     * @param ApplicationInterface $app
     *
     * @return Plant
     */
    public function addApp(ApplicationInterface $app) : self {
        $this->_apps[ $app->getName() ] = $app;
        return $this;
    }

    /**
     * @return ApplicationInterface[]
     */
    public function getApps() : array {
        return $this->_apps;
    }

    /**
     * @return int
     */
    public function getWorkersCount() : int {
        return $this->_count;
    }

    /**
     * Creates worker object
     * @return WorkerProcess
     */
    protected function _createWorker() : WorkerProcess {
        return new WorkerProcess();
    }

    public function inspect() {
        if($this->_dispatcher) { // in worker
            $this->_dispatcher->inspect();
        } else { // in master
            $current  = $this->getWorkersCount();
            $expected = call_user_func($this->_regulator, $this);
            if ($current < $expected) {
                for (; $current <= $expected; $current++) {
                    $worker = new WorkerProcess();
                    $this->_count++;
                    $worker
                        ->getIPC()
                        ->whenIncoming()
                        ->then($this->_messageHandler())
                        ->onFail([Log::class, "error"]);

                    $worker
                        ->whenExit()
                        ->then($this->_exitHandler())
                        ->onFail([Log::class, "error"]);

                    $worker
                        ->whenStarted()
                        ->then($this->_startHandler())
                        ->onFail([Log::class, "error"]);


                    $worker->start($this->_workerHandler());
                }
            } elseif ($current > $expected) {
                $for_stop = $current - $expected;
                $load = $this->getLoad();
                arsort($load);
                foreach ($load as $pid => $pload) {
                    if(!$for_stop--) {
                        Process::kill($pid, SIGTERM);
                    }
                }
            }
        }
    }

    /**
     * Handle messages from workers
     */
    protected function _messageHandler() {
        return function (Process\IPC\Message $message) {
            $worker = $message->context;
            /* @var WorkerProcess $worker */
            $msg = unserialize($message->data);
            if ($msg instanceof Daemon\IPC\InspectMessage) {
                $worker->setStats($msg->stats);
            } elseif ($msg instanceof Daemon\IPC\CloseMessage) {
                $worker->setStatus(WorkerProcess::STATUS_EXITED);
            } else {

            }
        };
    }

    /**
     * @return \Closure
     */
    protected function _exitHandler() {
        return function (WorkerProcess $worker) {
            $this->_count--;
            $worker->setStatus(WorkerProcess::STATUS_EXITED);
        };

    }

    /**
     * @return \Closure
     */
    protected function _startHandler() {
        return function (WorkerProcess $worker) {
            $worker->last_pong = time();
            $worker->setStatus(WorkerProcess::STATUS_WORKING);
            $this->_pids[$worker->getPID()] = $worker;
        };
    }

    /**
     * @return \Closure
     */
    protected function _workerHandler() {
        return function(Process\IPC $ipc) {
            $this->_pids       = [];
            $this->_dispatcher = new WorkerDispatcher($this, $ipc);
            $this->_dispatcher->run(...$this->_apps);
        };
    }

}