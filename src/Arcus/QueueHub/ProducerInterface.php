<?php

namespace Arcus\QueueHub;


use Arcus\TaskAbstract;

interface ProducerInterface {

    public function getProducerName() : string;

    public function __toString() : string;

    public function hasConsumers() : bool;

    public function countConsumers() : int;

    public function getConsumersNames() : array;

    /**
     * @param int $count
     *
     * @return ProducerInterface
     */
    public function setMaxSize(int $count);

    public function getMaxSize() : int;

    public function push(TaskAbstract $task) : bool;
}