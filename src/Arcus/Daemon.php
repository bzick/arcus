<?php

namespace Arcus;


use Arcus\Daemon\Worker;

class Daemon {

    /**
     * @var self
     */
    private static $_current;
    /**
     * @var float
     */
    protected $_timer = 2.0;
    protected $_workers = [];

    public static function getWorker() : Worker {
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
        $this->worker = new Worker($this, null);
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


    /**
     * @param mixed $count
     *
     * @return Worker
     */
    public function addWorker($count) : Worker {
        return $this->_workers[] = new Worker($this, $count);
    }

    public function start() {
        self::$_current = $this;
    }
}