<?php

namespace Arcus;


use Arcus\Daemon\Plant;
use Arcus\Daemon\Plant\RegulatorInterface;
use ION\Process;

/**
 * Мультипроцессовый демон.
 * Управляет группами процессов.
 * @package Arcus
 */
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
     * @var Plant[]
     */
    protected $_plants = [];
    /**
     * @var Cluster
     */
    protected $_cluster;
    /**
     * @var string
     */
    protected $_name;


    public function __construct(Cluster $cluster, string $name = '') {
        if(!$name) {
            $name = gethostname().":".Process::getPid();
        }
        $this->_name = $name;
        $this->_cluster = $cluster;
    }

    /**
     * @return Cluster
     */
    public function getCluster() : Cluster {
        return $this->_cluster;
    }

    /**
     * @return string
     */
    public function getName() : string {
        return $this->_name;
    }


    /**
     * Интервал между инспекциями подсистем.
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

    /**
     * Объявляет новую область процессов
     *
     * @param string $name
     * @param RegulatorInterface $regulator
     *
     * @return Plant
     */
    public function addPlant(string $name, RegulatorInterface $regulator) {
        $area = new Plant($this, $name, $regulator);
        $this->_plants[$area->getName()] = [ $area, $regulator ];
        return $area;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function getPlant(string $name) {
        return $this->_plants[$name][0] ?? false;
    }

    public function log($message, string $level) {
//        Log::{$level}();
    }

    /**
     * Запуск демона. Стартуют все фабрики.
     */
    public function start() {
        self::$_current = $this;
        foreach($this->_plants as $plant) {
            $plant->inspect();
        }
        \ION::interval($this->_timer)->then([$this, "inspect"]);
        \ION::dispatch();
        self::$_current = null;
    }

    /**
     * Инспекция всех фабрик
     */
    public function inspect() {
        foreach($this->_plants as $plant) {
            $plant->inspect();
        }
    }
}