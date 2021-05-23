<?php
namespace Appkita\PDFtoImage;
use Imageick;

Class Constanta {
    protected static function getConstants() {
        $oclas = new ReflectionClass('Imageick');
        return $oclas->getConstants();
    }
}