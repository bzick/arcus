<?php

namespace Arcus\Redis\HashStorage;


use Arcus\Redis\HashStorage;

class PreCached extends HashStorage {
    private $_values = [];

    public function offsetSet($offset, $value) {
        if(!isset($this->_values[$offset]) || $this->_values[$offset] != $value) {
            parent::offsetSet($offset, $value);
            $this->_values[$offset] = $value;
        }
    }

    public function offsetGet($offset) {
        if(isset($this->_values[$offset])) {
            return $this->_values[$offset];
        } else {
            return parent::offsetGet($offset);
        }
    }
} 