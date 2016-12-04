<?php

namespace Arcus;


use Arcus\QueueHub\ConsumerInterface;
use Arcus\QueueHub\ProducerInterface;

interface QueueHubInterface {

    /**
     * @param string $producer
     *
     * @return ProducerInterface
     */
    public function getProducer(string $producer) : ProducerInterface;

    public function getConsumer(string $queue_name, string $consumer) : ConsumerInterface;

}