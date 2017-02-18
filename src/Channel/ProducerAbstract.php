<?php

namespace Arcus\Channel;


abstract class ProducerAbstract implements ProducerInterface {

    protected $_channel = "";
    protected $_count   = 1000;

    public function __toString() : string {
        return get_called_class()."({$this->_channel})";
    }

    public function setMaxSize(int $count) {
        $this->_count = $count;
        return $this;
    }

    public function getMaxSize() : int {
        return $this->_count;
    }

    public function getName() : string {
        return $this->_channel;
    }
}