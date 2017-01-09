<?php

namespace Arcus\Daemon;


use Arcus\ApplicationAbstract;
use Arcus\Daemon\Area\ConstantRegulator;
use Arcus\Daemon\IPC\CloseMessage;
use Arcus\Daemon\IPC\InspectMessage;
use Arcus\ApplicationInterface;
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

    /**
     * @group dev
     */
    public function testBasicLifeCycle() {
        $stats = ["stats" => 1];
        $app = $this->getMockBuilder(TestApplication::class)
            ->setMethods(['enable', 'inspect', 'disable', 'halt'])
            ->getMock();

        $app->expects($this->once())
            ->method('enable')
            ->willReturn(true)
            ->with(
                $this->isInstanceOf(WorkerDispatcher::class)
            );

        $app->expects($this->once())
            ->method("inspect")
            ->willReturn($stats);

        $app->expects($this->once())
            ->method("disable")
            ->willReturn(\ION::promise(true));

        $app->expects($this->once())
            ->method("halt");


        $this->assertSame(1, $this->worker->run($app));
        $this->ipc->whenIncoming()->then(function (IPC\Message $message) {
            $msg = unserialize($message->data);
            $this->data[] = $msg;
            if($msg instanceof CloseMessage) {
                \ION::stop();
            }
        });
        $this->worker->inspector();
        $this->worker->stop();
        \ION::dispatch();

        $this->assertCount(2, $this->data);
        $this->assertInstanceOf(InspectMessage::class, $this->data[0]);
        $this->assertSame($stats, $this->data[0]->stats["entities"]["noname"]);
        $this->assertInstanceOf(CloseMessage::class, $this->data[1]);
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