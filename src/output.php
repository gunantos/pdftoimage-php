<?php
namespace Appkita\PDFtoImage;

class Output {
    private static $data = [];

    public function __set($name, $value) {
        self::$data[$name] = $value;
    }

    public function __get($name) {
        if (isset(self::$data[$name])) {
            return self::$data[$name];
        }
    }
}