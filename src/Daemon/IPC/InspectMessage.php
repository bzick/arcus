<?php

namespace Arcus\Daemon\IPC;


class InspectMessage
{
    public $stats;

    public function __construct(array $stats) {
        $this->stats = $stats;
    }

    public function getAppStats() {
        return $this->stats['entities'];
    }

    public function getAppStatsFor(string $name) {
        return $this->stats['entities'][$name] ?? [];
    }
}