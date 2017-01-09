<?php

namespace Arcus;


use Arcus\Channel\ConsumerInterface;
use Arcus\Channel\ProducerInterface;

interface ChannelFactory {

    /**
     * @param string $producer
     *
     * @return ProducerInterface
     */
    public function getProducer(string $producer) : ProducerInterface;

    public function getConsumer(string $channel_name, string $consumer) : ConsumerInterface;

}