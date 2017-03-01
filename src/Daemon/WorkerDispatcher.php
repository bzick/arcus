<?php

namespace Arcus\Daemon;


use Arcus\Cluster;
use Arcus\Daemon;
use Arcus\Daemon\IPC\CloseMessage;
use Arcus\Daemon\IPC\InspectMessage;
use Arcus\ApplicationInterface;
use Arcus\ChannelFactoryInterface;
use Arcus\RedisHub;
use ION\Process\IPC;
use ION\Promise;
use Psr\Log\LogLevel;

/**
 * Логика рабоего-процесса с приложениями
 * @package Arcus\Daemon
 */
class WorkerDispatcher {

    /**
     * @var ChannelFactoryInterface
     */
    private $_area;

    /**
     * @var ApplicationInterface[]
     */
    private $_apps = [];

    public function __construct(Plant $area, IPC $to_master) {
        $this->_area = $area;
        $this->_ipc = $to_master;
    }

    /**
     * @param ApplicationInterface[] ...$apps
     *
     * @return int the count of started applications
     */
    public function run(ApplicationInterface ...$apps) {
        foreach ($apps as $entity) {
            /** @var ApplicationInterface $entity */
            try {
                if($entity->enable($this)) {
                    $this->_apps[ $entity->getName() ] = $entity;
                } else {
                    $this->getDaemon()->log("The application {$entity->getName()} decided not to run", LogLevel::NOTICE);
                }
            } catch (\Throwable $e) {
                $entity->log($e, LogLevel::CRITICAL);
                $this->getDaemon()->log("The application {$entity->getName()} cannot be started: ".$e->getMessage(), LogLevel::ERROR);
            }
        }
        return count($this->_apps);
    }

    /**
     * Inspect all apps
     */
    public function inspector() {
        $stats = [
            "time"   => microtime(1),
            "worker" => \ION::getStats()
        ];
        foreach ($this->_apps as $name => $entity) {
            try {
                $stats["apps"][$name] = $entity->inspect();
            } catch (\Throwable $e) {
                $entity->log($e, LogLevel::ERROR);
            }
        }
        $this->_ipc->send(serialize(new InspectMessage($stats)));
    }

    /**
     * @param bool $force
     *
     * @return Promise
     */
    public function stop(bool $force = false) {
        return \ION::promise(function () use ($force) {
            foreach ($this->_apps as $name => $app) {
                if(!$force) {
                    $this->getDaemon()->log("Stopping $name application...", LogLevel::DEBUG);
                    try {
                        yield $app->disable();
                    } catch (\Throwable $e) {
                        $app->log($e, LogLevel::CRITICAL);
                        $this->getDaemon()->log("Occurred error while stopping application {$name}: ".$e->getMessage(), LogLevel::ERROR);
                    }
                }
                try {
                    $this->getDaemon()->log("Halting $name application...", LogLevel::DEBUG);
                    $app->halt();
                } catch (\Throwable $e) {
                    $this->getDaemon()->log("Occurred error while halting application {$name}: ".$e->getMessage(), LogLevel::ERROR);
                }
                $this->getDaemon()->log("Application $name has stopped", LogLevel::DEBUG);
            }
            $this->_ipc->send(serialize(new CloseMessage()));
        });
    }

    /**
     * @return Plant
     */
    public function getArea() : Plant {
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
     * @return ChannelFactoryInterface
     */
    public function getQueueHub() : ChannelFactoryInterface {
        return $this->getCluster()->getQueueHub();
    }


}