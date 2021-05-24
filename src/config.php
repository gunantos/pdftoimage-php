<?php
namespace Appkita\PDFtoImage;
use Imagick;
class Config {
    public static $format = 'png';
    public static $resolution = 144;
    public static $path = '';
    public static $prefix = 'convert';
    public static $layer_method = Imagick::LAYERMETHOD_FLATTEN;
    public static $colorspace;
    public static $quality;
    public static $count_page = 0;
    public static  $file = '';
}