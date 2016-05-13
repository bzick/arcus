<?php

$bus = new \Arcus\RedisHub();
$queue = new \Arcus\Redis\QueueHub($bus);
$cluster = new \Arcus\Cluster("cl", $bus, $queue);

$daemon = $cluster->addDaemon("imp01");

$worker = $daemon->addWorker(4);
$worker->setUser("www-data");
$worker->setGroup("www-data");

$server = new \Arcus\Server\HTTPServer("web", $cluster);


$worker->addApplication(new App());

$worker = $daemon->addWorker([4, 10, 0.7]);
$worker->addApplication(new App());

