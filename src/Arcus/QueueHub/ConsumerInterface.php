<?php

namespace Arcus\QueueHub;

use Arcus\TaskAbstract;
use ION\Sequence;

interface ConsumerInterface {

    public function getConsumerName() : string;

    public function getProducerName() : string;

    public function getCountTasks() : int;

    public function __toString() : string;

    /**
     * Возвращает список зарезервированных под выполенение задач
     * @return TaskAbstract[]
     */
    public function getReservedTasks() : array;

    /**
     * Release finished tasks automatically
     *
     * @param bool $state
     *
     * @return ConsumerInterface
     */
    public function setAutoRelease(bool $state);

    /**
     * How many tasks fetch from queue in one event.
     *
     * @param int $size
     *
     * @return ConsumerInterface
     */
    public function setBatchSize(int $size);

    /**
     * @param bool $state
     *
     * @return ConsumerInterface
     */
    public function autoEnable(bool $state);

    /**
     * @return Sequence
     */
    public function onTask() : Sequence;

    /**
     * @return ConsumerInterface
     */
    public function enable();

    /**
     * @return ConsumerInterface
     */
    public function disable();

    /**
     * Зачищает зарезерверованные на выполенение задачи
     * @return ConsumerInterface
     */
    public function release();

    /**
     * @return ConsumerInterface
     */
    public function close();
}