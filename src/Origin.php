<?php

namespace Arcus;

/**
 * Unique identifier of the event's origin
 */
class Origin implements \Serializable, \JsonSerializable {
    private $_name;

    /**
     * @param string $queue queue name
     * @param string $peer ID of origin
     * @return Origin
     */
    public static function make(string $queue, string $peer) {
        return new self($queue."#".$peer);
    }

    /**
     * @param string $name
     */
    public function __construct(string $name) {
        $this->_name = $name;
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return $this->_name;
    }

    /**
     * @return string
     */
    public function getName() : string {
        return $this->_name;
    }

    /**
     * Извлекает навание очереди
     * @return string
     */
    public function getQueue() : string {
        return $this->getAddress();
    }

    /**
     * Возвращает адрес приложения которому принадлежит источник
     * @return string
     */
    public function getAddress() : string {
        return strstr($this->_name, "#", true);
    }

    /**
     * Возвращает название кластера в котором возник источник
     * @return string
     */
    public function getClusterName() : string {
        return parse_url($this->_name, PHP_URL_SCHEME);
    }

    /**
     * Возвращает название узла в котором возник источник
     * @return string
     */
    public function getNodeName() : string {
        return parse_url($this->_name, PHP_URL_HOST);
    }

    /**
     * Возвращает ID воркера узла в котором возник источник
     * @return int
     */
    public function getWorkerID() : int {
        return parse_url($this->_name, PHP_URL_PORT);
    }

    /**
     * Возвращает название приложения в котором возник источник
     * @return string
     */
    public function getAppName() : string {
        return ltrim('/', parse_url($this->_name, PHP_URL_PATH));
    }

    /**
     * Извлекает ID источника
     * @return string
     */
    public function getPeer() : string {
        return parse_url($this->_name, PHP_URL_FRAGMENT);
    }

    public function getInfo() : array {
        $info = parse_url($this->_name);
        return [
            'cluster' => $info['scheme'],
            'node'    => $info['host'],
            'worker'  => $info['port'],
            'app'     => ltrim('/', $info['path']),
            'peer'    => $info['fragment'],
        ];
    }

    /**
     * @return string
     */
    public function serialize() {
        return $this->_name;
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized) {
        $this->_name = $serialized;
    }

    public function jsonSerialize() {
        return $this->_name;
    }

    public function __debugInfo() : array {
        return [
            "peer" => $this->getPeer(),
            "queue" => $this->getQueue(),
        ];
    }
}