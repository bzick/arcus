<?php

namespace Arcus\Kits;


class DevKit {


    /**
     * Dump binary data as hex
     * @param $data
     *
     * @return string
     */
    public static function binDump(string $data) {
        $len = strlen($data);
        $result = "";
        $bins = $chars = [];
        for($i = 0; $i < $len; $i++) {
            if($i && ($i % 4 === 0)) {
                $result .= implode(" ", $bins)."\n".implode(" ", $chars)."\n";
                $bins = $chars = [];
            }
            $bins[] = str_pad(decbin(ord($data{$i})), 8, '0', STR_PAD_LEFT);
            $chars[] = str_pad(preg_replace('/[^a-zA-Zа-яА-Я0-9 !@#$№;:"\'`\-+=,%^&*()\[\]<>\/|{}]/', '.', $data[$i]), 8, ' ', STR_PAD_LEFT);
        }
        if($bins) {
            $result .= implode(" ", $bins)."\n".implode(" ", $chars)."\n";
        }
        return $result;
    }

    /**
     * @param mixed $message
     * @return string
     */
    public static function dataToLog($message) {
        if($message instanceof \Exception) {
            return get_class($message).': '.$message->getMessage()." [".$message->getFile().":".$message->getLine()."]\n".$message->getTraceAsString();
        } elseif(!is_string($message)) {
            return var_export($message, true);
        } else {
            return $message;
        }
    }

    /**
     * Аналог var_dump но через self::dump()
     *
     * @param array $args
     */
    public static function varDump(...$args) {
        foreach($args as $arg) {
            echo self::dump($arg)."\n";
        }
    }

    /**
     * Аналогичен var_dump() за исключением того, взвращает дамп, а не выводит
     * на экран и не дампит сожержимое объектов
     * @see var_dump()
     * @param mixed $data Данные для вывода
     * @return string Отформатированные данные
     */
    public static function dump($data) {
        return self::_dump($data, '');
    }

    /**
     * Выполняет форматирование данных
     * @param mixed &$data Ссылка на данные, которые нужно отформатировать
     * @param string $tab Строка для отступа
     * @return string Отформатированные данные
     * @see dump()
     */
    private static function _dump(&$data, $tab = '  ') {
        if (is_array($data)) {
            $return = 'array(' . "\n";
            foreach ($data as $key => $value) {
                $return .= $tab . '[' . $key . '] => ' . self::_dump($value, $tab . '  ') . "\n";
            }
            return $return . $tab . ')';
        } elseif (is_object($data)) {
            if($data instanceof \ArrayObject) {
                $return = get_class($data) .'(' . "\n";
                foreach ((array)$data as $key => $value) {
                    $return .= $tab . '[' . $key . '] => ' . self::_dump($value, $tab . '  ') . "\n";
                }
                return $return . $tab . ')';
            } else {
                return get_class($data) . '(' . (method_exists($data, '__toString') ? ' ' . strval($data) : '') . ')';
            }
        } else {
            return var_export($data, true);
        }
    }
} 