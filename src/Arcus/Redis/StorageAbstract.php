<?php

namespace Arcus\Redis;


use Redis;

abstract class StorageAbstract implements \ArrayAccess, \Countable, \Serializable, \JsonSerializable {
    /**
     * @var Redis
     */
    protected $_redis;
    /**
     * @var string
     */
    protected $_name;


    public function __construct(\Redis $redis, $name) {
        $this->_redis = $redis;
        $this->_name  = $name;
    }

    /**
     * @param \Redis $redis
     */
    public function setRedis(\Redis $redis) {
        $this->_redis = $redis;
    }

    /**
     * @return Redis
     */
    public function getRedis() {
        return $this->_redis;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->_name;
    }


    /**
     * Whether a offset exists
     * @param mixed $offset
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($offset) {
        return $this->_exists($offset);
    }

    /**
     * Offset to retrieve
     * @param mixed $offset
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset) {
        return $this->_decode($this->_get($offset));
    }

    /**
     * Offset to set
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value) {
        $this->_set($offset, $this->_encode($value, $storage), $storage);
    }

    /**
     * Offset to unset
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset) {
        $this->_unset($offset);
    }

    /**
     * @param mixed $value
     * @param bool $storage
     * @return string
     */
    protected function _encode($value, &$storage = false) {
        if(is_object($value)) {
            $storage = $value instanceof self;
            return serialize($value);
        } else {
            $value = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if(json_last_error()) {
                throw new \RuntimeException("JSON encode failed: ".json_last_error_msg());
            }
            return $value;
        }
    }

    /**
     * @param string $value
     * @param bool $storage
     * @return mixed
     */
    protected function _decode($value, &$storage = false) {
        if($value === false) {
            return null;
        } elseif($value[0] === "O" || $value[0] === "C") {
            $value = unserialize($value);
            if($value instanceof self) {
                $storage = true;
                $value->setRedis($this->_redis);
            }
            return $value;
        } else {
            return json_decode($value, true);
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    abstract protected function _exists($name);

    /**
     * @param string $name
     * @return string
     */
    abstract protected function _get($name);

    /**
     * @param string $name
     * @param string $value
     * @return
     */
    abstract protected function _set($name, $value, $storage);

    /**
     * @param string $name
     */
    abstract protected function _unset($name);

    /**
     * Get all elements as is
     * @param string $name name may be changed
     * @return mixed
     */
    abstract protected function _all($name);

    /**
     * Convert redis hash to PHP array
     * @param bool $recursive convert to array hash elements
     * @return array
     */
    public function toArray($recursive = true) {
        $values = $this->_all($this->_name);
        if(!$values) {
            return [];
        }
        foreach($values as &$value) {
            $storage = false;
            $value = $this->_decode($value, $storage);
            if($storage && $recursive) {
                /* @var StorageAbstract $value */
                $value = $value->toArray(true);
            }
        }
        return $values;
    }


    /**
     * @param bool $recursive
     */
    public function clean($recursive = false) {
        if($recursive) {
            $prefix = '_deleting.'.intval(microtime(1) * 1e6).'.';
            if($this->_redis->rename($this->_name, $prefix.$this->_name)) {
                $values = $this->_all($prefix.$this->_name);
                foreach($values as &$value) {
                    $storage = false;
                    $value = $this->_decode($value, $storage);
                    if($storage) {
                        /* @var StorageAbstract $value */
                        $value->clean(true);
                    }
                }
                $this->_redis->del($prefix.$this->_name);
            }
        } else {
            $this->_redis->del($this->_name);
        }
    }


    /**
     * @return mixed
     */
    public function jsonSerialize() {
        return $this->toArray(true);
    }


    /**
     * String representation of object
     * @return string the string representation of the object or null
     */
    public function serialize() {
        return $this->_name;
    }

    /**
     * Constructs the object
     * @param string $serialized
     * @return void
     */
    public function unserialize($serialized) {
        $this->_name = $serialized;
    }

} 