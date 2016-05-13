<?php

namespace Arcus;


use Arcus\QueueHub\ConsumerInterface;
use Arcus\QueueHub\ProducerInterface;

interface QueueHubInterface {

    public function getProducer(string $producer) : ProducerInterface;

    public function getConsumer(string $producer, string $consumer) : ConsumerInterface;

    public function getAppConsumers(string $name) : array ;
    public function getAppProducer(string $name, string $type = 'cluster') : array ;
}