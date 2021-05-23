<?php
namespace Appkita\PDFtoImage;

class Config {
    public static string $format = 'jpg';
    public static int $resolution = 144;
    public static string $path = '';
    public static string $prefix = 'convert';
    public static $layer_method = Imageick::LAYERMETHOD_FLATTEN;
    public static $colorspace;
    public static $quality;
    public static int $count_page = 0;
    public static string $file = '';
}