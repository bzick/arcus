<?php

namespace Arcus\Daemon;


use Arcus\Daemon;
use Arcus\Daemon\Area\RegulatorInterface;
use Arcus\ApplicationInterface;
use Arcus\Log;
use Daemon\Area\WorkerProcess;
use ION\Process;

class Area {

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
     * @var int[]
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

    public function getLoadAverage() : float {
        return 0;
    }

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
     * @return Area
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
     * @return Area
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
     * @return Area
     */
    public function setPriority(int $priority) : self {
        $this->_priority = $priority;
        return $this;
    }

    /**
     * Add entity (application or server) to group of workers
     *
     * @param ApplicationInterface $entity
     *
     * @return Area
     */
    public function addApp(ApplicationInterface $entity) : self {
        $this->_apps[ $entity->getName() ] = $entity;
        return $this;
    }

    /**
     * @return ApplicationInterface[]
     */
    public function getApps() : array {
        return $this->_apps;
    }

    /**
     * @return array
     */
    public function inspectApps() {
        $stats = [];
        foreach($this->_apps as $name => $app) {
            try {
                $stats[$name] = $app->inspect();
            } catch(\Throwable $e) {
                Log::warning(new Daemon\Error\InspectionFailedException("Inspection of {$app} failed: ".$e->getMessage(), 0, $e));
            }
        }
        return $stats;
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
        $current = $this->getWorkersCount();
        $expected = call_user_func($this->_regulator, $this);
        if($current < $expected) {
            for(;$current <= $expected; $current++) {
                $worker = new WorkerProcess();
                $this->_count++;
                $worker
                    ->getIPC()
                    ->whenIncoming()
                    ->then([$this, "_messageHandler"])
                    ->onFail([Log::class, "error"]);

                $worker
                    ->whenExit()
                    ->then([$this, "_exitHandler"])
                    ->onFail([Log::class, "error"]);

                $worker
                    ->whenStarted()
                    ->then([$this, "_startHandler"])
                    ->onFail([Log::class, "error"]);


                $worker->start([$this, "_workerHandler"]);
            }
        } elseif($current > $expected) {
            for(;$current > $expected; $current--) {
//                Process::kill($pid, SIGTERM);
            }
        }
    }

    /**
     * Handle messages from workers
     *
     * @param Process\IPC\Message $message
     */
    protected function _messageHandler(Process\IPC\Message $message) {
        $worker = $message->context;
        /* @var WorkerProcess $worker */
        $msg = unserialize($message->data);
        if ($msg instanceof Daemon\IPC\InspectMessage) {
            $worker->setStats($msg->stats);
        } elseif ($msg instanceof Daemon\IPC\CloseMessage) {
            $worker->setStatus(WorkerProcess::STATUS_EXITED);
        } elseif ($msg instanceof Daemon\IPC\TaskMessage) {

        }
    }

    /**
     * Handle exits of workers
     * @param WorkerProcess $worker
     */
    protected function _exitHandler(WorkerProcess $worker) {
        $this->_count--;
        $worker->setStatus(WorkerProcess::STATUS_EXITED);
    }

    protected function _startHandler(WorkerProcess $worker) {
        $worker->last_pong = time();
        $worker->setStatus(WorkerProcess::STATUS_WORKING);
        $this->_pids[$worker->getPID()] = $worker;
    }

    protected function _workerHandler(Process\IPC $ipc) {
        $this->_dispatcher = new WorkerDispatcher($this, $ipc);
        $this->_dispatcher->run(...$this->_apps);
    }

}