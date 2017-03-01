<?php

namespace Arcus\Daemon\Plant;

use Arcus\Daemon\Plant;

interface RegulatorInterface {

    /**
     * @param Plant $area
     *
     * @return int new count of workers
     */
    public function __invoke(Plant $area) : int;
}