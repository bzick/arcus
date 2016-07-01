<?php

namespace Arcus\Daemon\Area;


use Arcus\Daemon\Area;

/**
 * Regulates the number of workers depending on the average load of workers
 * @package Arcus\Daemon\Area
 */
class LoadRegulator implements RegulatorInterface {

    public $min;
    public $max;
    public $warm_up_level = 0.7;
    public $cool_down_level = 0.5;
    public $step_size = 2;

    public function __construct(int $min, int $max) {
        $this->min = min($min, $max);
        $this->max = max($min, $max);
    }

    /**
     *
     * @param float $warm_up
     * @param float $cool_down
     *
     * @return LoadRegulator
     */
    public function setLoadLevel(float $warm_up, float $cool_down) : self {
        $this->warm_up_level = $warm_up;
        $this->cool_down_level = $cool_down;
        return $this;
    }

    /**
     * Amount workers
     *
     * @param int $step_size
     *
     * @return LoadRegulator
     */
    public function setStepSize(int $step_size) : self {
        $this->step_size = $step_size;
        return $this;
    }

    public function __invoke(Area $area) : int {
        $count = $area->getWorkersCount();
        if($count < $this->min) {
            $count = $this->min;
        }
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