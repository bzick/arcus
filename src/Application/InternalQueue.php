<?php

namespace Arcus\Application;


use Arcus\EntityInterface;
use Arcus\QueueHub\ConsumerInterface;
use Arcus\QueueHubInterface;
use ION\Sequence;

class InternalQueue
{
    const OWN     = 1;
    const DAEMON  = 2;
    const CLUSTER = 4;

    const ALL = self::OWN | self::DAEMON | self::CLUSTER;

    /**
     * @var ConsumerInterface
     */
    private $_own;
    /**
     * @var ConsumerInterface
     */
    private $_daemon;
    /**
     * @var ConsumerInterface
     */
    private $_cluster;

    public function __construct(QueueHubInterface $queue, EntityInterface $entity)
    {
        $this->_own     = $queue->getConsumer($entity->getURI('self'),    $entity->getURI('self'));
        $this->_daemon  = $queue->getConsumer($entity->getURI('daemon'),  $entity->getURI('self'));
        $this->_cluster = $queue->getConsumer($entity->getURI('cluster'), $entity->getURI('self'));
    }

    /**
     * @return ConsumerInterface
     */
    public function getSelfQueue() : ConsumerInterface {
        return $this->_own;
    }

    /**
     * @return ConsumerInterface
     */
    public function getDaemonQueue() : ConsumerInterface {
        return $this->_daemon;
    }

    /**
     * @return ConsumerInterface
     */
    public function getClusterQueue() : ConsumerInterface {
        return $this->_cluster;
    }

    public function enable(int $what = self::ALL)
    {
        if($what & self::OWN) {
            $this->_own->enable();
        }
        if($what & self::DAEMON) {
            $this->_daemon->enable();
        }
        if($what & self::CLUSTER) {
            $this->_cluster->enable();
        }
    }

    /**
     * @param int $what
     */
    public function disable(int $what = self::ALL)
    {
        if($what & self::OWN) {
            $this->_own->disable();
        }
        if($what & self::DAEMON) {
            $this->_daemon->disable();
        }
        if($what & self::CLUSTER) {
            $this->_cluster->disable();
        }
    }

    /**
     * @return Sequence
     */
    public function whenTask() : Sequence {
        $seq = new Sequence();
        $this->_own->whenTask()->then($seq);
        $this->_daemon->whenTask()->then($seq);
        $this->_cluster->whenTask()->then($seq);
        return $seq;
    }

    public function __call($name, $arguments)
    {
        $this->_own->$name(...$arguments);
        $this->_daemon->$name(...$arguments);
        $this->_cluster->$name(...$arguments);

        return $this;
    }
}