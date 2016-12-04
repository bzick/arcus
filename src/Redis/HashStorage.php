<?php

namespace Arcus\Redis;

/**
 * Обертка над хешом Redis.
 * Может вести себя как обычный массив, то есть $hash["one"] = 1 равносильно $redis->hSet($name, "one", 1).
 * Так как необходимо сохранять объекты то для преобразования значения в строку используется serialize().
 * Однако при преобразовании числа в строку через serialize() его не возможно атомарно инкрементировать через Redis.
 * Для решения данного конфликта только объекты преобразовываются через serialize(),
 * остальные значения преобразовываются через json_encode()
 * @package Arcus\Redis
 */
class HashStorage extends StorageAbstract {

    /**
     * @return string
     */
    public function __toString() {
        return self::class."[{$this->_name}]";
    }

    /**
     * Добавляет ссылку на хеш в ключ $key с возможность задать имя $name хеша в Redis
     * @param string $key название ключа в который необходимо лоложить ссылку на хеш
     * @param string $name если null то будет задано "{$name}[{$key}]"
     * @return HashStorage\PreCached
     */
    public function setHash($key, $name = null) {
        return $this[$key] = new HashStorage($this->_redis, $name?:"{$this->_name}[$key]");
    }

    /**
     * Добавляет ссылку на хеш с кешом в ключ $key с возможность задать имя $name хеша в Redis
     * @param string $key название ключа в который необходимо лоложить ссылку на хеш
     * @param string $name если null то будет задано "{$name}[{$key}]"
     * @return HashStorage\PreCached
     */
    public function setPreCachedHash($key, $name = null) {
        return $this[$key] = new HashStorage\PreCached($this->_redis, $name?:"{$this->_name}[$key]");
    }

    public function setSet($key, $name = null) {
        return $this[$key] = new SetStorage($this->_redis, $name?:"{$this->_name}[$key]");
    }

    public function setList($key, $name = null) {
        return $this[$key] = new ListStorage($this->_redis, $name?:"{$this->_name}[$key]");
    }

    /**
     * @param string $key
     * @param int $step
     * @return int
     */
    public function incrBy($key, $step) {
        return $this->_redis->hIncrBy($this->_name, $key, $step);
    }

    /**
     * Count elements of an object
     * @return int The custom count as an integer.
     */
    public function count() {
        return (int)$this->_redis->hLen($this->_name);
    }

    /**
     * @param string $name
     * @return array|false
     */
    protected function _all($name) {
        return $this->_redis->hGetAll($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function _exists($name) {
        return $this->_redis->hExists($this->_name, $name);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function _get($name) {
        return $this->_redis->hGet($this->_name, $name);
    }

    /**
     * @param string $name
     * @param string $value
     * @param $storage
     */
    protected function _set($name, $value, $storage) {
        if($name === null) {
            throw new \LogicException("Push don't works with hashes");
        }
        if($storage) {
            $this->_redis->sAdd($this->_name.'.objects', $name);
        }
        $this->_redis->hSet($this->_name, $name, $value);
    }

    /**
     * @param string $name
     */
    protected function _unset($name) {
        $this->_redis->multi();
        $this->_redis->hDel($this->_name, $name);
        $this->_redis->sRem($this->_name.'.objects', $name);
        $this->_redis->exec();
    }

    public function clean($recursive = false) {
        if($recursive) {
            $prefix = '_deleting.'.intval(microtime(1) * 1e6).'$';
            $this->_redis->multi();
            $this->_redis->rename($this->_name, $prefix.$this->_name);
            $this->_redis->rename($this->_name.'.objects', $prefix.$this->_name.'.objects');
            $result = $this->_redis->exec();
            if($result[1]) {
                $this->_cleanupObjects($prefix.$this->_name);
                $this->_redis->del($prefix.$this->_name.'.objects');
            }
            if($result[0]) {
                $this->_redis->del($prefix . $this->_name);
            }
        } else {
            $this->_redis->del($this->_name);
        }
    }

    protected function _cleanupObjects($name) {
        $keys = $this->_redis->sMembers($name.'.objects');
        $values = $this->_redis->hMGet($name, $keys);
        foreach($values as &$value) {
            $storage = false;
            $value = $this->_decode($value, $storage);
            if($storage) {
                /* @var StorageAbstract $value */
                $value->clean(true);
            }
        }
    }
}