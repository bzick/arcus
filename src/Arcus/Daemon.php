<?php

namespace Arcus;


use Arcus\Daemon\Area;
use Arcus\Daemon\Area\RegulatorInterface;
use ION\Process;

class Daemon {

    /**
     * @var self
     */
    private static $_current;
    /**
     * @var float
     */
    protected $_timer = 1.0;
    /**
     * @var Area[]
     */
    protected $_areas = [];

    public static function getWorker() : Area {
        if(self::$_current) {
            return self::$_current->worker;
        } else {
            throw new \LogicException("Daemon not ready");
        }
    }

    public static function getDaemon() : Daemon {
        if(self::$_current) {
            return self::$_current;
        } else {
            throw new \LogicException("Daemon not ready");
        }
    }

    public static function getCluster() : Cluster {
        if(self::$_current) {
            return self::$_current->cluster;
        } else {
            throw new \LogicException("Daemon not ready");
        }
    }

    public function __construct(Cluster $cluster, $name) {
        $this->cluster = $cluster;
        $this->worker = new Area($this, null);
        $this->_name = $name;
    }

    /**
     * Интервал между инспекциями подсистем. Влияет на таймауты коннектов.
     *
     * @param float $timeout
     *
     * @return Daemon
     */
    public function setInspectorTimeout(float $timeout) : self {
        $this->_timer = $timeout;
        return $this;
    }

    /**
     * Возвращает интервал времени через который запускается инспектор
     * @return float
     */
    public function getInspectorTimeout() : float {
        return $this->_timer;
    }


    public function addArea(Area $area, RegulatorInterface $regulator) {
        $this->_areas[$area->getName()] = [$area, $regulator];
    }

    public function getArea(string $name) {
        return $this->_areas[$name] ?? false;
    }

    /**
     * Запуск демона
     */
    public function start() {
        self::$_current = $this;
        foreach($this->_areas as list($area, $regulator)) {
            /* @var Area $area */
            /* @var RegulatorInterface $regulator */
            $current = $area->getWorkersCount();
            $expected = $regulator($area);
            if($current < $expected) {
                for(;$current <= $expected; $current++) {
                    $worker = Process::spawn()->run([$area, "setup"]);
                    $worker->onConnect()->then([$area, "addWorker"]);
                    $worker->onMessage()->then([$area, "message"]);
                    $worker->onDisconnect()->then([$area, "removeWorker"]);
                    $worker->onExit()->then([$this, "rebalance"]);
                }
            } elseif($current > $expected) {
                for(;$current > $expected; $current--) {
//                    Process::kill($pid, SIGTERM);
                }
            }

        }
        \ION::interval($this->_timer)->then([$this, "rebalance"]);
        \ION::dispatch();
        self::$_current = null;

    }
}