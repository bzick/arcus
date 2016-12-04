<?php

namespace Arcus\Daemon;


use Arcus\Cluster;
use Arcus\Daemon;
use Arcus\Daemon\Error\InspectionFailedException;
use Arcus\EntityInterface;
use Arcus\QueueHubInterface;
use Arcus\RedisHub;
use Daemon\Error\StartupFailedException;
use ION\Process\IPC;
use Psr\Log\LogLevel;

class Worker {

    const WORKER = 1;
    /**
     * @var QueueHubInterface
     */
    private $_area;

    private $_entities = [];

    public function __construct(Area $area, IPC $to_master) {
        $this->_area = $area;
        $this->_ipc = $to_master;
    }

    public function run(EntityInterface ...$entities) {
        foreach ($entities as $name => $entity) {
            try {
                if($entity->enable($this)) {
                    $this->_entities[ $name ] = $entity;
                } else {
                    $this->getDaemon()->log("The application {$name} decided not to run", LogLevel::NOTICE);
                }
            } catch (\Throwable $e) {
                $entity->log($e, LogLevel::CRITICAL);
                $this->getDaemon()->log(
                    new StartupFailedException("Еhe application {$name} cannot be started because of an error", 0, $e),
                    LogLevel::ERROR
                );
            }
        }
        if(count($this->_entities)) {
            return true;
        } else {
            $this->getDaemon()->log("No one application", LogLevel::ALERT);
            return false;
        }
    }

    /**
     * @return Area
     */
    public function getArea() : Area {
        return $this->_area;
    }

    /**
     * @return Daemon
     */
    public function getDaemon() : Daemon {
        return $this->getArea()->getDaemon();
    }

    /**
     * @return Cluster
     */
    public function getCluster() : Cluster {
        return $this->getDaemon()->getCluster();
    }

    /**
     * @return RedisHub
     */
    public function getRedisHub() : RedisHub {
        return $this->getCluster()->getRedisHub();
    }

    /**
     * @return QueueHubInterface
     */
    public function getQueueHub() : QueueHubInterface {
        return $this->getCluster()->getQueueHub();
    }

    public function inspector() {
        foreach ($this->getArea()->getEntities() as $entity) {

        }
    }
}