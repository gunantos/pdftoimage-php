<?php
namespace Appkita\PDFtoImage;
/**
 * PDF TO IMAGE CONVERTER
 * By: Gunanto Simamora
 * 
 * 
 */
use \Imagick;
use Appkita\PDFtoImage\Exceptions\InvalidFormat;
use Appkita\PDFtoImage\Exceptions\PdfDoesNotExist;
use Appkita\PDFtoImage\Exceptions\PageDoesNotExist;
use Appkita\PDFtoImage\IMAGE;
use Appkita\PDFtoImage\Helpers;
use Appkita\PDFtoImage\GS;
Class Convert {
    use \Appkita\PDFtoImage\Config;
    private $_output;
    private $page = 0;

    function __construct(string $file='', array $config=[]) {
        $this->OS = Helpers::getOS();
        $this->cekOSExtendsion();
        if (!empty($file)) {
            $this->setFile($file);
        }
        $this->_output = new \stdClass();
        $this->_output->filename = [];
        $this->_output->error = [];
        $this->initConfig($config);
        if (empty($this->path)) {
            $dir = \dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
            if (!\file_exists($dir)) {
                \mkdir($dir, 0777);
            }
            $this->path = $dir;
        }
    }

    public function __get($name) {
       return $this->_get_config($name);
    }

    public function __set($name, $value) {
        $name = \strtolower($name);
        $this->_set_config($name, $value);
    }

    public function config($key='') {
        return $this->_get_config($key);
    }

    public function page(int $page = null) {
        return $this->set_page($page)->page;
    }

    public function set_page($page) {
        if (!empty($page)) {
            if ($page <= $this->count_page) {
                $this->page = $page;
            } else {
                throw new PageNotExists("Page `{$page}` not exist. Count page only {$this->count_page}");
            }
        }
        return $this;
    }

    private function _convert_command($filename = null) {
        $gs = new GS();
        $file_convert = $gs->toIMG($this->file, $this->path, $this->format, $this->page, $this->resolution, $this->quality, $this->prefix);
        foreach($file_convert as $f) {
            array_push($this->_output->filename, $f);
        }
        return $this;
    }

    private function _convert_imagick($filename = null) {
        $imagick = new Imagick();
        if (isset($this->resolution) && $this->resolution > 0) {
                 $imagick->setResolution($this->resolution, $this->resolution);
        }
       if (isset($this->size['width']) && isset($this->size['height']) && $this->size['width'] > 0 && $this->size['height'] > 0) {
           $imagick->setSize($this->size['width'], $this->size['height']);
       }
        if (!empty($this->colorspace)){
            $imagick->setColorspace($this->colorspace);
        }
        if (!empty($this->compress)) {
            $imagick->setCompression($this->compress);
        }
        if ($this->compress_quality > 0) {
            $imagick->setCompressionQuality($this->compress_quality);
        }
        if ($this->page > 0) {
            $imagick->readImage(sprintf('%s[%s]', $this->file, ($this->page - 1)));
        } else {
            $imagick->readImage($this->file);
        }
        if (!empty($this->layer_method) && is_int($this->layer_method)) {
            $imagick->mergeImageLayers($this->layer_method);
        }
        $imagick->setFormat($this->format);
        foreach($imagick as $i=> $imagick) {
            $output = $this->_create_filename(($i + 1), $filename);
            $imagick->writeImage($output);
            array_push($this->_output->filename, $output);
        }
        $imagick->clear();
        return $this;
    }
    
    public function run(int $page = null, string $output = '')
    {
        $this->set_page($page);
        $filename = '';
        if (\is_array($output)) {
            $this->initConfig($output);
        } else {
            $filename = $output;
        }
        
        $ttl = $this->getCount();
        if ($this->count_page > 0) {
            if ($this->useType == IMAGE::GHOSTSCRIPT) {
                $this->_convert_command();
            } else {
                $this->_convert_imagick();
            }
        }
        return $this->_output->filename;
    }

    private function _create_filename($indeks = 0, string $filename='') {
        if (!empty($filename) && is_dir($filename)) {
            $this->_set_config('path', $output);
        }
        if (!empty($filename) && !is_dir($filename)) {
            $ext = \pathinfo($file, PATHINFO_EXTENSION);
            $_tmp = \str_replace('.'.$ext, '', $filename);
            $filename = $_temp.'-'. $indeks .'-of-'. $this->count_page .'.'. $ext;
        } else {
            $pdffilename = basename($this->file,".pdf");
        
            $pathname =str_replace(' ', '_', $pdffilename);
            $pathname = preg_replace('/[^A-Za-z0-9\_]/', '', $pathname);
        
            $image_path =  $this->_get_config('path', true).$pathname.DIRECTORY_SEPARATOR;

            if (!\file_exists($image_path)) {
                @mkdir($image_path, 0777);
            }
            $prefix = $this->_get_config('prefix', true);
            if (!empty($prefix)) {
                $image_path .= $prefix;
            }
            if ($indeks > 0) {
                $image_path .= '-'. $indeks;
            }
            $filename = $image_path . $this->_get_config('format', true);
        }
        return $filename;
    }

    public function output() {
        return $this->_output;
    }

    public function error() {
        return $this->_output->error;
    }
}