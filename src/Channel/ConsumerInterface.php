<?php

namespace Arcus\Channel;

use Arcus\TaskAbstract;
use ION\Sequence;

/**
 * Потребитель задач.
 * Получает и обрабатывает задачи из очереди.
 *
 * @package Arcus\QueueHub
 */
interface ConsumerInterface {

    public function getName() : string;

    public function getChannelsNames() : array;

    public function getCountTasks() : int;

    public function __toString() : string;

    /**
     * Подсисывает потребителя на канал
     * @param string $channel
     *
     * @return int
     */
    public function subscribe(string $channel) : int;

    /**
     * Подписан ли потребитель на этот канал
     *
     * @param string $channel
     *
     * @return bool
     */
    public function hasChannel(string $channel) : bool;

    /**
     * Возвращает список зарезервированных под выполенение задач
     * @return TaskAbstract[]
     */
    public function getReservedTasks() : array;

    /**
     * Release finished tasks automatically. Default: true
     *
     * @param bool $state
     *
     * @return ConsumerInterface
     */
    public function setAutoRelease(bool $state);

    /**
     * How many tasks fetch from queue in one event. Default: 1
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
    public function whenTask() : Sequence;

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