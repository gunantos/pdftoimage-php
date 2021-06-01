<?php
namespace Appkita\PDFtoImage;
use \Imagick;
use \Appkita\PDFtoImage\Exceptions\InvalidFormat;
use \Appkita\PDFtoImage\Exceptions\PdfDoesNotExist;
use \Appkita\PDFtoImage\Exceptions\PageDoesNotExist;

Class Convert {
    use \Appkita\PDFtoImage\Config;
    private $_output;
    private $page = 0;

    function __construct(string $file='', array $config=[]) {
        if (!empty($file)) {
            $this->setFile($file);
        }
        $this->_output = new \stdClass();
        $this->_output->filename = [];
        $this->_output->error = [];
        $this->initConfig($config);
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

    private function _convert($filename = null) {
        $imagick = new Imagick();
        if (isset($this->resolution['x']) && isset($this->resolution['y'])) {
            if ($this->resolution['x'] > 0 && $this->resolution['y'] > 0) {
                 $imagick->setResolution($this->resolution['x'], $this->resolution['y']);
            }
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
        if ($this->count_page > 0) {
            $this->_convert($filename);
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
            $filename = $this->_get_config('path', true).$pdffilename.'-'.$this->_get_config('prefix', true);
            if ($indeks > 0) {
                $filename .= $indeks;
            }
            $filename .= $this->_get_config('format', true);
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