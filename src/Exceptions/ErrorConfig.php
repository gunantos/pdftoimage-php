<?php
namespace Appkita\PDFtoImage\Exceptions;
use \Exception;

class ErrorConfig extends Exception {
    public static function forResololutionNotSupport($e) {
        return (new static("{$e} not support the resolution"));
    }

    public static function forNotSupportLibrary($e) {
         return (new static("{$e} not support library"));
    }

    public static function forNotSupportOS() {
        return (new static("Not Support Your Operation System"));
    }
}