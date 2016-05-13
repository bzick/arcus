<?php

namespace Arcus\Daemon;


use Arcus\Daemon;
use Arcus\EntityInterface;
use ION\Process;

class Worker {

    private $_user;
    private $_group;
    private $_priority;
    private $_count;
    private $_chdir;
    /**
     * @var EntityInterface[]
     */
    private $_apps = [];

    public function __construct(Daemon $daemon, $count) {
        $this->_daemon = $daemon;
        $this->_count = $count;
    }

    public function setWorkDir(string $path) {
        $this->_chdir = realpath($path);
        if($this->_chdir) {
            throw new \InvalidArgumentException("Directory $path doesn't exists.");
        }
        return $this;
    }

    /**
     * @param string $user_name
     *
     * @return Worker
     */
    public function setUser(string $user_name) : self {
        if(!Process::userExists($user_name)) {
            throw new \RuntimeException("User $user_name does not exists");
        }
        $this->_user = $user_name;
        return $this;
    }

    /**
     * @param string $group_name
     *
     * @return Worker
     */
    public function setGroup(string $group_name) : self {
        if(!Process::groupExists($group_name)) {
            throw new \RuntimeException("Group $group_name does not exists");
        }
        $this->_group = $group_name;
        return $this;
    }

    /**
     * @param int $priority
     *
     * @return Worker
     */
    public function setPriority(int $priority) : self {
        $this->_priority = $priority;
        return $this;
    }

    /**
     * @param EntityInterface $entity
     *
     * @return Worker
     */
    public function addApplication(EntityInterface $entity) : self {
        $this->_apps[ $entity->getName() ] = $entity;
        return $this;
    }

    /**
     * @return array
     */
    public function inspect() {
        $stats = [];
        foreach($this->_apps as $name => $app) {
            try {
                $stats[$name] = $app->inspect();
            } catch(\Throwable $e) {
                // todo something
            }
        }
        return $stats;
    }

    /**
     * Run worker and apps
     */
    public function start() {
        if($this->_chdir) {
            chdir($this->_chdir);
        }
        if($this->_priority != null) {
            Process::setPriority($this->_priority);
        }
        if($this->_user) {
            Process::setUser($this->_user, $this->_group);
        }
        foreach($this->_apps as $name => $app) {
            try {
                $app->enable();
            } catch(\Throwable $e) {
                // todo some thing
            }
        }
    }



}