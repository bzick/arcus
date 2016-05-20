<?php

namespace Arcus\Daemon\Area;


use Arcus\Daemon\Area;

class LoadRegulator implements RegulatorInterface {

    public $min;
    public $max;
    public $load_level = 0.7;
    public $step_size = 2;

    public function __construct(int $min, int $max) {
        $this->min = min($min, $max);
        $this->max = max($min, $max);
    }

    public function setLoadLevel(float $load_level) : self {
        $this->load_level = $load_level;
        return $this;
    }

    public function setStepSize(int $step_size) : self {
        $this->step_size = $step_size;
        return $this;
    }

    public function __invoke(Area $area) : int {
        $count = $area->getWorkersCount();
        if($area->getLoadAverage() <= $this->load_level) {
            return $count;
        }
        if($count < $this->min) {
            if($this->min - $count >= $this->step_size) {
                $count = $this->min;
            } else {
                $count += $this->step_size;
            }
        } else {
            $count += $this->step_size;
        }
        if($count > $this->max) {
            $count = $this->max;
        }
        return $count;
    }
}