<?php

namespace Arcus\Daemon;


use Arcus\Daemon;
use Arcus\EntityInterface;
use Arcus\Log;
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
     * @var EntityInterface[]
     */
    private $_entities = [];

    /**
     * Area constructor.
     *
     * @param string $name
     * @param int|callable $regulator
     */
    public function __construct(string $name, $regulator) {
        $this->name = $name;
        if(is_int($regulator)) {
            $this->_regulator = new Daemon\Area\ConstantRegulator($regulator);
        } elseif (is_callable($regulator)) {
            $this->_regulator = $regulator;
        } else {
            throw new \InvalidArgumentException("Invalid regulator value");
        }
    }

    public function getWorkersCount() : int {
        return $this->_count;
    }

    public function getLoadAverage() : array {
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
     * @param EntityInterface $entity
     *
     * @return Area
     */
    public function addEntity(EntityInterface $entity) : self {
        $this->_entities[ $entity->getName() ] = $entity;
        return $this;
    }

    /**
     * @return array
     */
    public function inspect() {
        $stats = [];
        foreach($this->_entities as $name => $app) {
            try {
                $stats[$name] = $app->inspect();
            } catch(\Throwable $e) {
                // todo something
            }
        }
        return $stats;
    }

    private function _masterMessage(Process\IPC\Message $message) {

    }

    private function _masterExit(Process\Worker $worker) {
        Log::emerge("Lost connection with master. Terminate worker");
//        Process::kill(SIGTERM);
    }

    private function _workerMessage(Process\IPC\Message $message) {

    }

    private function _workerExit(Process\Worker $worker) {
        if($worker->getExitStatus()) {
            Log::emerge("Lost connection with wot. Terminate worker");
        }
    }

    private function _spawn() {
        $worker = new Process\Worker();
        $worker->onMessage()->then([$this, "_workerMessage"]);
        $worker->onExit()->then([$this, "_workerExit"]);
        $worker->run(function (Process\Worker $master) {
            $master->onMessage()->then([$this, "_masterMessage"]);
            $master->onExit()->then([$this, "_masterExit"]);
            if($this->_work_dir) {
                chdir($this->_work_dir);
            }
            if($this->_priority != null) {
                Process::setPriority($this->_priority);
            }
            if($this->_user) {
                Process::setUser($this->_user, $this->_group);
            }
            \ION::interval(1.0, "arcus.inspector")->then($this->getInspector($master));
            foreach($this->_entities as $name => $app) {
                try {
                    $app->enable();
                } catch(\Throwable $e) {
                    Log::alert(new \RuntimeException("Application $name could not enable", 0, $e));
                }
            }
        });
    }

    public function getInspector(Process\Worker $master) {
        return function () use ($master) {
            $stats = [
                "load"     => \ION::getStats(),
                "entities" => []
            ];
            foreach($this->_entities as $name => $app) {
                try {
                    $stats["entities"][$name] = $app->inspect();
                } catch(\Throwable $e) {
                    Log::alert(new \RuntimeException("Application $name could not enable", 0, $e));
                }
            }
            $master->message("arcus.stats")->withData(serialize($stats));
        };
    }

    /**
     * Run worker and apps
     */
    public function start() {
        $count = call_user_func($this->_regulator, $this);
        if($count > $this->_count) {
            for($i = $this->_count; $i < $count; $this->_count++) {
                $this->_spawn();
            }
        } else {
            for($i = $this->_count; $i > $count; $this->_count--) {
                $this->_spawn();
            }
        }
    }



}