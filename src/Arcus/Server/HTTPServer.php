<?php
/**
 * Created by PhpStorm.
 * User: bzick
 * Date: 05.04.16
 * Time: 22:47
 */

namespace Arcus\Server;


use Arcus\EntityInterface;
use Arcus\QueueHubInterface;
use ION\Stream\Server;
use Psr\Log\LogLevel;

class HTTPServer implements EntityInterface {

    public $name = __CLASS__;
    public $server;

    public function __construct($name) {
        $this->name = $name;
        $this->server = new Server();
    }

    public function listen($host, $backlog = -1) {
        return $this->server->listen($host, $backlog);
    }

    public function getName() {
        return $this->name;
    }

    public function __toString() {
        return $this->name;
    }

    public function enable() {
        $this->server->enable();
    }

    public function disable() {
        $this->server->disable();
    }

    public function halt() {
//        $this->server->;
    }

    public function inspect() {
        return $this->server->getStats();
    }

    public function log($message, $level = LogLevel::DEBUG) {
        // TODO: Implement log() method.
    }

    public function logRotate() {
        // TODO: Implement logRotate() method.
    }

    public function fatal(\Exception $error) {
        // TODO: Implement fatal() method.
    }
}