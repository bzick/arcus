<?php

namespace Arcus;



use ION\Process;
use ION\Promise;
use Psr\Log\LogLevel;

interface EntityInterface {

    public function getName();

    public function __toString();

    public function enable(QueueHubInterface $queue) : bool;

    public function disable() : Promise;

    public function halt() : Process;

    public function inspect();

	public function log($message, $level = LogLevel::DEBUG);

    public function dispatch(TaskAbstract $task);

	public function logRotate();

	public function fatal(\Exception $error);

} 