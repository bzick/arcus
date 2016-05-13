<?php

namespace Arcus;



use Psr\Log\LogLevel;

interface EntityInterface {

    public function getName();

    public function __toString();

    public function enable();

    public function disable();

    public function halt();

    public function inspect();

	public function log($message, $level = LogLevel::DEBUG);

	public function logRotate();

	public function fatal(\Exception $error);

} 