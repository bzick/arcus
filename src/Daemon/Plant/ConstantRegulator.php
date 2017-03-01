<?php

namespace Arcus\Daemon\Plant;


use Arcus\Daemon\Plant;

class ConstantRegulator implements RegulatorInterface {

    public $count = 0;

    public function __construct(int $count) {
        $this->count = $count;
    }

    public function __invoke(Plant $area) : int {
        return $this->count;
    }
}