<?php

namespace Arcus\Redis;


class SetStorage extends StorageAbstract {

    public function add($value) {
        return $this->_redis->sAdd($this->_name, $this->_encode($value));
    }

    public function rem($value) {
        return $this->_redis->sRem($this->_name, $this->_encode($value));
    }

    public function count() {
        return $this->_redis->sCard($this->_name);
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function _exists($name) {
        return $this->_redis->sIsMember($this->_name, $name);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function _get($name) {
        return $name;
    }

    /**
     * @param string $name
     * @param string $value
     * @param bool $storage
     * @return void
     */
    protected function _set($name, $value, $storage) {
        if($name !== null) {
            throw new \LogicException("Set storage does not have keys");
        }
        $this->_redis->sAdd($this->_name, $value);
    }

    /**
     * @param string $name
     */
    protected function _unset($name) {
        $this->_redis->sRem($this->_name, $name);
    }

    /**
     * Get all elements as is
     * @param string $name name may be changed
     * @return mixed
     */
    protected function _all($name) {
        $this->_redis->sMembers($name);
    }
}