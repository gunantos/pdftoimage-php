<?php
namespace Appkita\PDFtoImage;

class Output {
    private static $data = [];

    public static function __set($name, $value) {
        self::$data[$name] = $value;
    }

    public static function __get($name) {
        if (isset(self::$data[$name])) {
            return self::$data[$name];
        }
    }
}