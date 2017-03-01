<?php

namespace Arcus\Daemon\Plant;


use ION\Process\ChildProcess;

class WorkerProcess extends ChildProcess
{
    const STATUS_PENDING  = 'pending';
    const STATUS_WORKING  = 'working';
    const STATUS_EXITING  = 'exiting';
    const STATUS_EXITED   = 'exited';

    public $status = self::STATUS_PENDING;

    public $last_pong = 0;

    public $load = 0.0;

    public $statistic = [];

    public function setStatus($status) {
        $this->last_pong = $status['time'];
        if($status['worker']) {
            $this->load = $status['worker']['php_time'] / ($status['worker']['current_timestamp'] - $status['worker']['reset_timestamp']);
        }
    }

    public function setStats(array $statistic) {
        $this->statistic = $statistic;
    }

    public function getAppStats() {
        return $this->statistic['apps'];
    }

    public function getAppStatsFor(string $name) : array {
        return $this->statistic['apps'][$name] ?? [];
    }
}