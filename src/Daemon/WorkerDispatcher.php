<?php

namespace Arcus\Daemon;


use Arcus\Cluster;
use Arcus\Daemon;
use Arcus\EntityInterface;
use Arcus\QueueHubInterface;
use Arcus\RedisHub;
use ION\Process\IPC;
use ION\Promise;
use Psr\Log\LogLevel;

class WorkerDispatcher {

    /**
     * @var QueueHubInterface
     */
    private $_area;

    /**
     * @var EntityInterface[]
     */
    private $_entities = [];

    public function __construct(Area $area, IPC $to_master) {
        $this->_area = $area;
        $this->_ipc = $to_master;
    }

    /**
     * @param EntityInterface[] ...$entities
     *
     * @return int the count of started entities
     */
    public function run(EntityInterface ...$entities) {
        foreach ($entities as $name => $entity) {
            /** @var EntityInterface $entity */
            try {
                if($entity->enable($this)) {
                    $this->_entities[ $name ] = $entity;
                } else {
                    $this->getDaemon()->log("The application {$name} decided not to run", LogLevel::NOTICE);
                }
            } catch (\Throwable $e) {
                $entity->log($e, LogLevel::CRITICAL);
                $this->getDaemon()->log("The application {$name} cannot be started: ".$e->getMessage(), LogLevel::ERROR);
            }
        }
        return count($this->_entities);
    }

    /**
     * Inspect all entities
     */
    public function inspector() {
        $stats = [
            "time"   => microtime(1),
            "worker" => \ION::getStats()
        ];
        foreach ($this->_entities as $name => $entity) {
            try {
                $stats["entities"][$name] = $entity->inspect();
            } catch (\Throwable $e) {
                $entity->log($e, LogLevel::ERROR);
            }
        }
        $this->_ipc->send(json_encode($stats, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param bool $force
     *
     * @return Promise
     */
    public function stop(bool $force = false) {
        return \ION::promise(function () use ($force) {
            foreach ($this->_entities as $name => $entity) {
                if(!$force) {
                    $this->getDaemon()->log("Stopping $name application...", LogLevel::DEBUG);
                    try {
                        yield $entity->disable();
                    } catch (\Throwable $e) {
                        $entity->log($e, LogLevel::CRITICAL);
                        $this->getDaemon()->log("Occurred error while stopping application {$name}: ".$e->getMessage(), LogLevel::ERROR);
                    }
                }
                try {
                    $this->getDaemon()->log("Halting $name application...", LogLevel::DEBUG);
                    $entity->halt();
                } catch (\Throwable $e) {
                    $this->getDaemon()->log("Occurred error while halting application {$name}: ".$e->getMessage(), LogLevel::ERROR);
                }
                $this->getDaemon()->log("Application $name has stopped", LogLevel::DEBUG);
            }
        });
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


}