<?php

namespace Arcus;

use Arcus\Kits\DevKit;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Log {

    const NATIVE_LEVELS = [
        LogLevel::DEBUG     => E_USER_NOTICE,
        LogLevel::INFO      => E_USER_NOTICE,
        LogLevel::NOTICE    => E_USER_NOTICE,
        LogLevel::WARNING   => E_USER_WARNING,
        LogLevel::ERROR     => E_USER_ERROR,
        LogLevel::CRITICAL  => E_USER_ERROR,
        LogLevel::ALERT     => E_USER_ERROR,
        LogLevel::EMERGENCY => E_USER_ERROR,
    ];

    /**
     * @var LoggerInterface
     */
    private static $_instance;

    public static function setLoggerInstance(LoggerInterface $instance) {
        self::$_instance = $instance;
    }

    /**
     * @param mixed $message
     */
    public static function debug($message) {
        self::message(LogLevel::DEBUG, $message);
    }

    /**
     * @param mixed $message
     */
    public static function info($message) {
        self::message(LogLevel::INFO, $message);
    }

    /**
     * @param mixed $message
     */
    public static function notice($message) {
        self::message(LogLevel::NOTICE, $message);
    }

    /**
     * @param mixed $message
     */
    public static function warning($message) {
        self::message(LogLevel::WARNING, $message);
    }

    /**
     * @param mixed $message
     */
    public static function error($message) {
        self::message(LogLevel::ERROR, $message);
    }

    /**
     * @param mixed $message
     */
    public static function critical($message) {
        self::message(LogLevel::CRITICAL, $message);
    }

    /**
     * @param mixed $message
     */
    public static function alert($message) {
        self::message(LogLevel::ALERT, $message);
    }

    /**
     * @param mixed $message
     */
    public static function emerge($message) {
        self::message(LogLevel::EMERGENCY, $message);
    }

    /**
     * @param string $level
     * @param mixed $message
     */
    public static function message($level, $message) {
        $message = DevKit::dataToLog($message);
        if(self::$_instance) {
            self::$_instance->log($level, $message, []);
        } else {
            trigger_error($message, self::NATIVE_LEVELS[$level]);
        }
    }

}