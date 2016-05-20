<?php

namespace Arcus\Daemon\Area;

use Arcus\Daemon\Area;

interface RegulatorInterface {

    /**
     * @param Area $area
     *
     * @return int new count of workers
     */
    public function __invoke(Area $area) : int;
}