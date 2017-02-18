<?php

namespace Arcus;


use Arcus\Channel\ConsumerInterface;
use Arcus\Channel\ProducerInterface;

interface ChannelFactoryInterface {

    /**
     * @param string $producer
     *
     * @return ProducerInterface
     */
    public function getProducer(string $producer) : ProducerInterface;

    /**
     * @param string $consumer
     *
     * @return ConsumerInterface
     */
    public function getConsumer(string $consumer) : ConsumerInterface;

    /**
     * Отравить задачу в канал
     *
     * @param string $channel
     * @param TaskAbstract $task
     *
     * @return bool
     */
    public function push(string $channel, TaskAbstract $task) : bool;

}