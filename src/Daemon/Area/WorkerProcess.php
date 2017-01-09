<?php

namespace Daemon\Area;


use ION\Process\ChildProcess;

class WorkerProcess extends ChildProcess
{
    const STATUS_PENDING  = 'pending';
    const STATUS_WORKING  = 'working';
    const STATUS_EXITING  = 'exiting';
    const STATUS_EXITED   = 'exited';

    public $status = self::STATUS_PENDING;

    public $last_pong = 0;

    public $statistic = [];

    public function setStatus($status) {
        $this->status = $status;
    }

    public function setStats(array $statistic) {
        $this->statistic = $statistic;
    }

    public function getStats() {
        return $this->statistic["worker"];
    }

    public function getAppStats() {
        return $this->statistic['entities'];
    }

    public function getAppStatsFor(string $name) : array {
        return $this->statistic['entities'][$name] ?? [];
    }
}