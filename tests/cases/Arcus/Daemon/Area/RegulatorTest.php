<?php

namespace Arcus\Daemon\Area;


use Arcus\Daemon\Area;

class RegulatorTest extends \PHPUnit_Framework_TestCase{

    public function testConstRegulator() {

        $regulator = new ConstantRegulator(5);

        $this->assertEquals(5, $regulator(new class extends Area {

        }));
    }

    /**
     * @group dev
     */
    public function testLoadRegulator() {

    }
}