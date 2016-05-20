<?php

namespace Arcus\Daemon\Area;


use Arcus\Daemon\Area;

class ConstantRegulator implements RegulatorInterface {

    public $count = 0;

    public function __construct(int $count) {
        $this->count = $count;
    }

    public function __invoke(Area $area) : int {
        return $this->count;
    }
}