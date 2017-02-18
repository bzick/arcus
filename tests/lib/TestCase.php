<?php

namespace Arcus;

use Arcus\Redis\RedisChannelFactory;

class TestCase extends \PHPUnit\Framework\TestCase {
    /**
     * @var RedisHub
     */
    public $redis;
    /**
     * @var Cluster
     */
    public $cluster;
    /**
     * @var RedisChannelFactory
     */
    public $queue;
    public $daemon;

    public $data = [];

    public function setUp() {
        $this->redis = new RedisHub();
        $this->redis->setRedisHost('redis', [REDIS_HOST, REDIS_PORT], REDIS_DATABASE);
        $this->redis->redis->flushAll();
        $this->queue = new RedisChannelFactory($this->redis->queue);
        $this->cluster = new Cluster("ghost", $this->redis, $this->queue);
        $this->daemon = $this->cluster->addDaemon('daemo');
    }

    public function tearDown() {
        $this->data = [];
        $this->daemon = null;
        $this->queue = null;
        $this->cluster = null;
        $this->redis->close();
        $this->redis = null;
    }
}