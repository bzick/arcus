<?php

namespace Arcus\Daemon\Plant;

use Arcus\Daemon\Plant;

interface RegulatorInterface {

    /**
     * @param Plant $plant
     *
     * @return int new count of workers
     */
    public function __invoke(Plant $plant) : int;
}