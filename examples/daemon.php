<?php

use Arcus\Cluster;
use Arcus\Daemon\Area;
use Arcus\Daemon\Area\ConstantRegulator;
use Arcus\Redis\QueueHub;
use Arcus\RedisHub;

$bus     = new RedisHub();
$queue   = new QueueHub($bus->queue);
$cluster = new Cluster("cl", $bus, $queue);

$daemon = $cluster->addDaemon("imp01");

//$worker = $daemon->addWorker(4);

$frontend = new Area("frontend", new ConstantRegulator(10));
$frontend->setUser("www-data");
$frontend->setGroup("www-data");

$server = new \Arcus\Server\HTTPServer("web", $cluster);


$frontend->addEntity(new App());

$daemon->addArea($frontend);

$backend = new Area('backend', (new Area\LoadRegulator(4, 10))->setLoadLevel(0.5)->setStepSize(1));

$worker = $daemon->addWorker([4, 10, 0.7]);
$worker->addEntity(new App());

