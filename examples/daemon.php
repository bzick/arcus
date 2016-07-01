<?php

use Arcus\Cluster;
use Arcus\Daemon\Area;
use Arcus\Daemon\Area\ConstantRegulator;
use Arcus\Redis\QueueHub;
use Arcus\RedisHub;

// prepare tools

$bus     = new RedisHub();
$queue   = new QueueHub($bus->queue);
$cluster = new Cluster("cl", $bus, $queue);

// now create daemon

$daemon = $cluster->addDaemon("imp01");

$frontend = new Area("frontend", new ConstantRegulator(10));
$frontend->setUser("www-data");
$frontend->setGroup("www-data");
$frontend->setPriority(20);

$server = new \Arcus\Server\HTTPServer("web");

$frontend->addEntity($server);

$daemon->addArea($frontend, new ConstantRegulator(3));

$backend = new Area('backend');
$backend->setUser("nobody");
$backend->setGroup("nobody");
$backend->setPriority(3);
$backend->addEntity(new class("app") extends \Arcus\ApplicationAbstract {
    // ...
});

$daemon->addArea($backend, (new Area\LoadRegulator(4, 10))->setLoadLevel(0.7, 0.5)->setStepSize(2));

$daemon->start();

