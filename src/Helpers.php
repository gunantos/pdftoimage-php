<?php
namespace Appkita\PDFtoImage;
use Appkita\PDFtoImage\IMAGE;
class Helpers {
    public static function getOS() {
        switch (true) {
            case stristr(PHP_OS, 'DAR'): return IMAGE::OS_OSX;
            case stristr(PHP_OS, 'WIN'): return IMAGE::OS_WIN;
            case stristr(PHP_OS, 'LINUX'): return IMAGE::OS_LINUX;
            default : return IMAGE::OS_UNKNOWN;
        }
    }

    public static function isFileExistPath(string $path, $file) {
        if (!\is_array($file)) {
            $file = [$file];
        }
        $path = str_replace('\\', DIRECTORY_SEPARATOR, \str_replace('/', DIRECTORY_SEPARATOR, $path));
        $path = \rtrim($path, DIRECTORY_SEPARATOR);
        foreach($file as $fl) {
            if (!\file_exists($path.DIRECTORY_SEPARATOR.$fl)) {
                return false;
            }
        }
        return true;
    }
}