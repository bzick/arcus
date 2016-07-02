<?php

namespace Arcus;
use Arcus\Kits\DevKit;

/**
 * Центральное хранилище соединений к redis.
 * Каждое свойство - соединение к redis, которое может быть отдельным соединением.
 * @package Arcus
 * @property \Redis $sessions Redis для хранения сессий BackEnd
 * @property \Redis $stats    Redis для статистики и списка соединений FrontEnd и BackEnd
 * @property \Redis $queue    Redis для коммуникаций между FrontEnd и BackEnd
 * @property \Redis $cache    Redis для кеширования
 * @property \Redis $redis    Redis по умолчанию
 * @property \Redis $r        псевдоним к $redis
 */
class RedisHub {
    /**
     * @var array
     */
    private $_hosts   = [
        "redis" => [
            "connect" => ['127.0.0.1', 6379],
            "db" => 0
        ]
    ];
    /**
     * @var \Redis[]
     */
    private $_drivers = [];

    /**
     * Добавить хост по умолчанию
     * @see setHost
     * @param string $name
     * @param string|array $host
     * @param int $db
     * @return $this
     */
    public function setRedisHost($name, $host, $db = 0) {
        return $this->setHost($name, $host, $db);
    }

    /**
     * Добавить хост
     * @param string $name
     * @param string|array $host
     * @param int $db
     * @return $this
     */
    public function setHost($name, $host, $db = 0) {
        if(isset($this->_drivers[$name])) {
            throw new \LogicException("Redis connection '$name' already established");
        }
        $_host = [];
        if(is_array($host)) {
            $_host['connect'] = $host;
        } else {
            $_host['connect'] = [$host, 0];
        }
        $_host['db'] = $db;
        $this->_hosts[$name] = $_host;
        return $this;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getHost($name) {
        if (isset($this->_hosts[$name])) {
            $host = $this->_hosts[$name];
            $this->$name;
            if($host['connect'][1]) {
                return $host['connect'][0].":".$host['connect'][1];
            } else {
                return $host['connect'][0];
            }
        } else {
            return $this->getHost('redis');
        }
    }

    /**
     * @param string $name
     * @return \Redis
     */
    private function _connect($name) {
        if (isset($this->_hosts[$name])) {
            $host = $this->_hosts[$name];
            $driver = new \Redis();
            $driver->connect($host['connect'][0], $host['connect'][1]);
            $driver->host = $host['connect'][0];
            $driver->port = $host['connect'][1];
            if($host['db']) {
                $driver->db = $host['db'];
                $driver->select($host['db']);
            }
            $driver->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);
            return $this->_drivers[$name] = $driver;
        } elseif(isset($this->_drivers['redis'])) {
            return $this->_drivers['redis'];
        } else {
            return $this->_drivers['redis'] = $this->_connect('redis');
        }
    }

    /**
     * @param string $key
     * @return \Redis
     */
    public function __get($key) {
        if(isset($this->_drivers[$key])) {
            return $this->$key = $this->_drivers[$key];
        } else {
            return $this->$key = $this->_connect($key);
        }
    }

    /**
     * Пинг всех соединений
     */
    public function ping() {
        foreach($this->_drivers as $name => $driver) {
            try {
                $driver->ping();
            } catch (\Exception $e) {
                trigger_error("Redis ($name): ".DevKit::dataToLog($e), E_USER_ERROR);
                $driver->close();
                $host = $this->_hosts[$name];
                $driver->connect($host['connect'][0], $host['connect'][1]);
            }
        }
        return $this;
    }

    /**
     * Закрыть все соединения
     * @return $this
     */
    public function close() {
        foreach($this->_drivers as $name => $driver) {
            try {
                $driver->close();
            } catch (\Exception $e) {
                trigger_error("Redis ($name):".DevKit::dataToLog($e), E_USER_WARNING);
            }
        }
        return $this;
    }

	/**
	 * Удаляет, но не отключает все драйверы redis в хранилище
	 */
    public function reset() {
        foreach($this->_drivers as $name => $driver) {
            unset($this->$name);
        }
        $this->_drivers = [];
    }

    /**
     * Переоткрывает соединения
     */
    public function connect() {
        foreach($this->_drivers as $name => $driver) {
            $host = $this->_hosts[$name];
            if(!$driver->connect($host['connect'][0], $host['connect'][1])) {
	            throw new \RuntimeException("Failed to connect to host: ".implode(":", $host['connect']));
            }
            if($host['db']) {
                $driver->select($host['db']);
            }
        }
    }
}