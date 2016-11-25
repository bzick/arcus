<?php
/**
 * Created by PhpStorm.
 * User: bzick
 * Date: 25.11.16
 * Time: 23:58
 */

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
        $this->cluster = new Cluster("intest", $this->redis, $this->queue);
        $this->daemon = $this->cluster->addDaemon('n1');
    }

    public function tearDown() {
        $this->shared = [];
        $this->daemon = null;
        $this->queue = null;
        $this->cluster = null;
        $this->redis->close();
        $this->redis = null;
    }
}