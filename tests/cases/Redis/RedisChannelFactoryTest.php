<?php

namespace Arcus\Redis;


use Arcus\Channel\ConsumerInterface;
use Arcus\Channel\ProducerInterface;
use Arcus\CustomTask;
use Arcus\TaskAbstract;
use Arcus\TestCase;

class RedisChannelFactoryTest extends TestCase {


    public function testFactory() {
        $producer = $this->queue->getProducer('producer');
        $consumer = $this->queue->getConsumer('consumer');
        $this->assertInstanceOf(ProducerInterface::class, $producer);
        $this->assertInstanceOf(ConsumerInterface::class, $consumer);

        $this->assertEquals('producer', $producer->getName());
        $this->assertEquals([], $consumer->getChannelsNames());
        $this->assertEquals('consumer', $consumer->getName());
        $consumer->subscribe("producer");
        $consumer->subscribe("producer1");
        $this->assertEquals(['producer', 'producer1'], $consumer->getChannelsNames());
    }
    /**
     * @group dev
     */
    public function testTransferTask() {
        $producer = $this->queue->getProducer('producer');
        $consumer = $this->queue->getConsumer('consumer');

        $producer->push(new CustomTask(1));
        $producer->push(new CustomTask(2));

        $this->assertEquals(2, $producer->getCountTasks());
        $this->assertEquals(2, $consumer->getCountTasks());

        $consumer->whenTask()->then(function(TaskAbstract $task) use ($consumer) {
            $this->data["tasks"][] = $task;
            $this->assertEquals([$task], $consumer->getReservedTasks());
            if(count($this->data["tasks"]) == 2) {
                \ION::stop();
            }
        })->onFail(function(\Throwable $e) {
            \ION::stop();
            $this->data["error"] = $e;
        });
        $consumer->subscribe("producer")->autoEnable(true)->enable()->setAutoRelease(true);
        $this->assertEquals(['consumer'], $producer->getConsumersNames());

        \ION::dispatch();

        $this->assertArrayNotHasKey("error", $this->shared);
        $this->assertEquals([
            new CustomTask(1), new CustomTask(2)
        ], $this->shared['tasks']);
    }
}