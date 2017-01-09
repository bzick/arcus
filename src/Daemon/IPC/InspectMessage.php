<?php

namespace Arcus\Daemon\IPC;


class InspectMessage
{
    public $stats;

    public function __construct(array $stats) {
        $this->stats = $stats;
    }
}