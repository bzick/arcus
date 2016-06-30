<?php

namespace Arcus\Redis;


use Arcus\CustomTask;
use Arcus\QueueHub\ConsumerInterface;
use Arcus\QueueHub\ProducerInterface;
use Arcus\TaskAbstract;

class QueueHubTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var QueueHub
     */
    public $hub;
    public $shared = [];

    public function setUp() {
        $redis = new \Redis();
        $redis->host = REDIS_HOST;
        $redis->port = REDIS_PORT;
        $redis->database = REDIS_DATABASE;
        $redis->connect(REDIS_HOST, REDIS_PORT, REDIS_DATABASE);
        $redis->select(REDIS_DATABASE);
        $redis->flushDB();
        $this->shared = [];
        $this->hub = new QueueHub($redis);
    }

    public function testFactory() {
        $producer = $this->hub->getProducer('prod');
        $consumer = $this->hub->getConsumer('prod', 'cons');
        $this->assertInstanceOf(ProducerInterface::class, $producer);
        $this->assertInstanceOf(ConsumerInterface::class, $consumer);

        $this->assertEquals('prod', $producer->getProducerName());
        $this->assertEquals('prod', $consumer->getProducerName());
        $this->assertEquals('cons', $consumer->getConsumerName());
    }

    public function testTransferTask() {
        $producer = $this->hub->getProducer('prod');
        $consumer = $this->hub->getConsumer('prod', 'cons');

        $producer->push(new CustomTask(1));
        $producer->push(new CustomTask(2));

        $this->assertEquals(2, $producer->getCountTasks());
        $this->assertEquals(2, $consumer->getCountTasks());

        $consumer->onTask()->then(function(TaskAbstract $task) use ($consumer) {
            $this->shared["tasks"][] = $task;
            $this->assertEquals([$task], $consumer->getReservedTasks());
            if(count($this->shared["tasks"]) == 2) {
                \ION::stop();
            }
        })->onFail(function(\Throwable $e) {
            \ION::stop();
            $this->shared["error"] = $e;
        });
        $consumer->autoEnable(true)->enable()->setAutoRelease(true);
        $this->assertEquals(['cons'], $producer->getConsumersNames());

        \ION::dispatch();

        $this->assertArrayNotHasKey("error", $this->shared);
        $this->assertEquals([
            new CustomTask(1), new CustomTask(2)
        ], $this->shared['tasks']);
    }
}