<?php

namespace Arcus;

use Arcus\Redis\QueueHub;

class TestCase extends \PHPUnit_Framework_TestCase {
    /**
     * @var RedisHub
     */
    public $redis;
    /**
     * @var Cluster
     */
    public $cluster;
    /**
     * @var QueueHub
     */
    public $queue;
    public $daemon;

    public $shared = [];

    public function setUp() {
        $this->redis = new RedisHub();
        $this->redis->setRedisHost('redis', [REDIS_HOST, REDIS_PORT], REDIS_DATABASE);
        $this->redis->redis->flushAll();
        $this->queue = new QueueHub($this->redis->queue);
        $this->cluster = new Cluster("ghost", $this->redis, $this->queue);
        $this->daemon = $this->cluster->addDaemon('daemo');
    }

    public function tearDown() {
        $this->shared = [];// ghost://frontend1:123/http#
        $this->daemon = null;
        $this->queue = null;
        $this->cluster = null;
        $this->redis->close();
        $this->redis = null;
    }
}