<?php

namespace Arcus\Daemon;


use Arcus\ApplicationAbstract;
use Arcus\Daemon\Area\ConstantRegulator;
use Arcus\EntityInterface;
use Arcus\TestApplication;
use Arcus\TestCase;
use ION\Process\IPC;

class WorkerDispatcherTest extends TestCase
{

    /**
     * @var WorkerDispatcher
     */
    public $worker;
    /**
     * @var IPC
     */
    public $ipc;

    public function setUp()
    {
        parent::setUp();
        $area = new Area($this->daemon, "unit", new ConstantRegulator(5));
        list($ipc, $this->ipc) = IPC::create(1,1);
        $this->worker = new WorkerDispatcher($area, $ipc);
    }

    public function testRun() {
        $app = $this->getMockBuilder(TestApplication::class)
            ->setMethods(['enable'])
            ->getMock();

        $app->expects($this->once())
            ->method('enable')
            ->willReturn(true)
            ->with(
                $this->isInstanceOf(WorkerDispatcher::class)
            );

        $this->assertSame(1, $this->worker->run($app));
    }

    public function testNotRun() {
        $app = $this->getMockBuilder(TestApplication::class)
            ->setMethods(['enable'])
            ->getMock();

        $app->expects($this->once())
            ->method('enable')
            ->willReturn(false)
            ->with(
                $this->isInstanceOf(WorkerDispatcher::class)
            );
        $this->assertSame(0, $this->worker->run($app));

    }
}