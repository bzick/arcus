<?php

use Arcus\Cluster;
use Arcus\Daemon\Area;
use Arcus\Daemon\Area\ConstantRegulator;
use Arcus\Redis\QueueHub;
use Arcus\RedisHub;
use Arcus\TaskAbstract;

// Cluster -> daemon -> area -> process -> entity
// prepare tools

$bus     = new RedisHub();
$queue   = new QueueHub($bus->queue);
$cluster = new Cluster("CLS01", $bus, $queue);

// create daemon
$daemon = $cluster->addDaemon();

$frontend = $daemon->addArea('servers', new ConstantRegulator(3));
//$frontend = $daemon->addArea('servers', Regulator::constant(5));
$frontend->setUser("www-data");
$frontend->setGroup("www-data");
$frontend->setPriority(20);

//$server = new \Arcus\Server\HTTPServer("web");

$server = $frontend->addApp(Arcus\Server\HTTPServer::class, ["web" => "echo"]);
//$frontend->addEntity($server);

//$backend = $daemon->addArea('apps', Regulator::loadAverage(4, 10))->setLoadLevel(0.7, 0.5)->setStepSize(2));
$backend = $daemon->addArea('apps', (new Area\LoadRegulator(4, 10))->setLoadLevel(0.7, 0.5)->setStepSize(2));
$backend->setUser("nobody");
$backend->setGroup("nobody");
$backend->setPriority(3);

$backend->addApp(Arcus\Application\EchoServer::class, "echo");

$daemon->start();

