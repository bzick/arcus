<?php

namespace Arcus\Channel;


use Arcus\TaskAbstract;

/**
 * Источник задач.
 * Добавляет задачи в очередь.
 *
 * @package Arcus\QueueHub
 */
interface ProducerInterface {

    public function getName() : string;

    public function __toString() : string;

    /**
     * @return bool
     */
    public function hasConsumers() : bool;

    /**
     * @return int
     */
    public function getCountConsumers() : int;

    /**
     * Текущее количество задач в очереди
     *
     * @return int
     */
    public function getCountTasks() : int;

    /**
     * @return array
     */
    public function getConsumersNames() : array;

    /**
     * Максимально допустимый размер очереди
     *
     * @param int $count
     *
     * @return ProducerInterface
     */
    public function setMaxSize(int $count);

    /**
     * @return int
     * @see setMaxSize
     */
    public function getMaxSize() : int;

    /**
     * @param TaskAbstract $task
     *
     * @return bool true если очередь успешно добавлена в очередь иначе false
     */
    public function push(TaskAbstract $task) : bool;
}