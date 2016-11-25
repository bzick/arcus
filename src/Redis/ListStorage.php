<?php

namespace Arcus\Redis;


class ListStorage extends StorageAbstract {

    public function push() {

    }

    public function pop() {

    }

    public function shift() {

    }

    public function unshift() {

    }

    protected function _exists($index) {
        return $index < $this->_redis->lLen($this->_name);
    }

    protected function _get($index) {
        return $this->_redis->lIndex($this->_name, intval($index));
    }

    protected function _set($index, $value, $storage) {
        if($index === null) {
            $this->_redis->rPush($this->_name, $value);
        } else {
            $this->_redis->lSet($this->_name, $index, $value);
        }
    }

    /**
     * Offset to unset
     * @param int $offset
     * @return void
     */
    protected function _unset($offset) {
//        $this->_redis->lTrim($this->_name, $offset, $offset + 1);
    }

    /**
     * Count elements
     * @return int
     */
    public function count() {
        return (int)$this->_redis->lLen($this->_name);
    }

    /**
     * Get all elements as is
     * @param string $name name may be changed
     * @return mixed
     */
    protected function _all($name) {
        return $this->_redis->lRange($name, 0, -1);
    }
}