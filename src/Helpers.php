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
}