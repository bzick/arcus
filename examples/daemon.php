<?php

use Arcus\Cluster;
use Arcus\Daemon\Plant;
use Arcus\Redis\RedisChannelFactory;
use Arcus\RedisHub;

// Cluster -> daemon -> plant -> process -> application
// prepare tools

$bus     = new RedisHub();
$queue   = new RedisChannelFactory($bus->queue);
$cluster = new Cluster("CLS01", $bus, $queue);

// create daemon
$daemon = $cluster->addDaemon();

$frontend = $daemon->addPlant('servers', new Plant\ConstantRegulator(3));
//$frontend = $daemon->addArea('servers', Regulator::constant(5));
$frontend->setUser("www-data");
$frontend->setGroup("www-data");
$frontend->setPriority(20);

$server = new \Arcus\Server\WebServer("web");

$server = $frontend->addApp($server);
//$frontend->addEntity($server);

//$backend = $daemon->addArea('apps', Regulator::loadAverage(4, 10))->setLoadLevel(0.7, 0.5)->setStepSize(2));
$backend = $daemon->addPlant('apps', (new Plant\LoadRegulator(4, 10))->setLoadLevel(0.7, 0.5)->setStepSize(2));
$backend->setUser("nobody");
$backend->setGroup("nobody");
$backend->setPriority(3);

//$backend->addApp(Arcus\Application\EchoServer::class, "echo");

$daemon->start();

