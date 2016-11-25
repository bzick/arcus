<?php

namespace Arcus\QueueHub;


use Arcus\Daemon;
use Arcus\EntityInterface;
use Arcus\QueueHubInterface;

class ClusterConsumer {
    public $app;
    public $daemon;
    public $cluster;

    public function __construct(QueueHubInterface $queue, EntityInterface $entity) {
        $consumer = Daemon::current()->worker()->getName()."/".$entity->getName();
        $this->app = $queue->getConsumer($consumer, $consumer);
        $this->daemon = $queue->getConsumer(Daemon::current()->getName()."/".$entity->getName(), $consumer);
        $this->cluster = $queue->getConsumer(Daemon::current()->getCluster()->getName()."/".$entity->getName(), $consumer);
    }
}