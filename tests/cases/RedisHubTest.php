<?php

namespace Arcus;

use Arcus\Kits\DevKit;

class RedisHubTest extends TestCase {

    /**
     * @var RedisHub
     */
    public $redis;

    public function testDefault() {
        $redis = $this->redis;

        $this->assertInstanceOf('\Redis', $redis->redis);
        $this->assertInstanceOf('\Redis', $redis->r);
        $this->assertInstanceOf('\Redis', $redis->another_redis);
        $this->assertTrue($redis->redis === $redis->r);
        $this->assertTrue($redis->redis === $redis->another_redis);
        $this->assertEquals('+PONG', $redis->r->ping());
        $redis->ping();
    }

    public function testCustom() {
        $redis = $this->redis;
        $redis->setRedisHost('cache', [REDIS_HOST, REDIS_PORT], 11);
        $this->assertFalse($redis->redis === $redis->cache);
        $this->assertFalse($redis->r === $redis->cache);
        $this->assertFalse($redis->another_redis === $redis->cache);
        $this->assertTrue($redis->r->set("unit.test:key", 1));
        $this->assertEquals('1', $redis->r->get("unit.test:key"));
        $this->assertFalse($redis->cache->get("unit.test:key"));
    }

    public function testReconnect() {
        $redis = $this->redis;
        $redis->connect();
        $this->assertEquals('+PONG', $redis->r->ping());
    }

    public function testConnectClosed() {
        $redis = $this->redis;
        $redis->close();
        $redis->connect();
        $this->assertEquals('+PONG', $redis->r->ping());
    }

    /**
     * @group rh-fork
     */
    public function testFork() {
        $this->setUp();
        $redis = $this->redis;
        $redis->close();
        $pid = pcntl_fork();
        if($pid == -1) {
            $this->assertTrue($pid >= 0, "fork");
        } elseif($pid) { // parent
            $redis->connect();
            usleep(1e4);
            $this->assertEquals('10', $redis->r->get("unit.test:key"));
        } else { // child
            $redis->connect();
            try {
                $redis->r->set("unit.test:key", 10);
            } catch(\Exception $e) {
                error_log(DevKit::dataToLog($e));
            }
            $redis->close();
            exit;
        }
    }
}