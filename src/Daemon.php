<?php

namespace Arcus;


use Arcus\Daemon\Area;
use Arcus\Daemon\Area\RegulatorInterface;
use Arcus\Daemon\Worker;
use ION\Process;
use Psr\Log\LogLevel;

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
    /**
     * @var Cluster
     */
    protected $_cluster;
    /**
     * @var string
     */
    protected $_name;

    public static function getCurrentWorker() : Worker {
        return self::getCurrent()->getWorker();
    }

    public static function getCurrent() : Daemon {
        if(self::$_current) {
            return self::$_current;
        } else {
            throw new \LogicException("Daemon not ready");
        }
    }

    public static function getCurrentCluster() : Cluster {
        return self::getCurrent()->getCluster();
    }

    public function __construct(Cluster $cluster, string $name = '') {
        if(!$name) {
            $name = gethostname().":".Process::getPid();
        }
        $this->_name = $name;
        $this->_cluster = $cluster;
    }

    public function getCluster() : Cluster {
        return $this->_cluster;
    }

    public function getName() : string {
        return $this->_name;
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

    public function addArea(string $name, RegulatorInterface $regulator) {
        $area = new Area($this, $name, $regulator);
        $this->_areas[$area->getName()] = [ $area, $regulator ];
        return $area;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function getArea(string $name) {
        return $this->_areas[$name][0] ?? false;
    }

    public function log($message, string $level) {
        iF($this->_logger) {

        } else {
//            error_log();
        }
    }

    /**
     * Запуск демона
     */
    public function start() {
        self::$_current = $this;
        foreach($this->_areas as $area) {

            $area->inspect();
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