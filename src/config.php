<?php
namespace Appkita\PDFtoImage;
use Imagick;
trait Config {
    protected $format = 'png';
    protected $width = 144;
    protected $height = 144;
    protected $path = '';
    protected $prefix = 'convert';
    protected $layer_method = Imagick::LAYERMETHOD_FLATTEN;
    protected $colorspace;
    protected $quality;
    protected $count_page = 0;
}