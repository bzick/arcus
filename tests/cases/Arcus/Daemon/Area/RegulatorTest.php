<?php

namespace Arcus\Daemon\Area;


use Arcus\Daemon\Area;

class RegulatorTest extends \PHPUnit_Framework_TestCase {

    public function testConstRegulator() {

        $regulator = new ConstantRegulator(5);

        $this->assertEquals(5, $regulator(new class("test") extends Area {
            public function getLoadAverage() : float {
                return 1.0;
            }
        }));
    }

    /**
     * @group dev
     */
    public function testLoadRegulator() {
        $regulator = new LoadRegulator(2, 6);
        $regulator->setLoadLevel(0.5, 0.4);
        $regulator->setStepSize(2);

        $area = new class("test") extends Area {
            public $load;
            public function setLoadAverage(float $load) {
                $this->load = $load;
                return $this;
            }

            public function getLoadAverage() : float {
                return $this->load;
            }
        };

        $this->assertSame(2, $regulator($area->setLoadAverage(0.0)));
        $this->assertSame(2, $regulator($area->setLoadAverage(0.4)));
        $this->assertSame(4, $regulator($area->setLoadAverage(0.6)));
        $this->assertSame(4, $regulator($area->setLoadAverage(0.4)));
    }
}