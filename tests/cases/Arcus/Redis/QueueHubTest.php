<?php

namespace Arcus\Redis;


use Arcus\QueueHub\ConsumerInterface;
use Arcus\QueueHub\ProducerInterface;

class QueueHubTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var QueueHub
     */
    public $hub;

    public function setUp() {
        $redis = new \Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT);
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
}