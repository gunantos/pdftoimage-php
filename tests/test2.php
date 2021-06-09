<?php

require_once "../vendor/autoload.php";

use Appkita\PDFtoImage\Convert;
use Appkita\PDFtoImage\IMAGE;

$file = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'tests'.DIRECTORY_SEPARATOR.'test.pdf';
$gs = new Convert($file, [
    'useType'=>IMAGE::GHOSTSCRIPT,
    'path'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'output'
]);

$output = $gs->run();
die(json_encode($output));