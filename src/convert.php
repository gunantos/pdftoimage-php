<?php
namespace Appkita\PDFtoImage;
use \Imagick;
use \Appkita\PDFtoImage\Config;
use \Appkita\PDFtoImage\Output;
use \Appkita\PDFtoImage\Exceptions\PdfDoesNotExist;
use \Appkita\PDFtoImage\Exceptions\PageDoesNotExist;


Class Convert {
    use Config;
    private $_output;

    function __construct(string $file='', array $config=[]) {
        if (!empty($file)) {
            $this->setFile($file);
        }
        $this->_output = new Output();
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

    private function _convert(string $file = '', array $config=[], $page = null) {
        if (!empty($file)) {
            $this->setFile($file);
        }
        if (\sizeof($config) > 0) {
            $this->initConfig($config);
        }
        $this->set_page($page);
        $imagick = new Imagick();
        $imagick->setResolution($this->resolution, $this->resolution);
        if (!empty($this->colorspace)){
            $imagick->setColorspace($this->colorspace);
        }
        if (!empty($this->quality)) {
            $imagick->setCompressionQuality($this->quality);
        }
        $halaman = $this->page - 1;
        $imagick->readImage(sprintf('%s[%s]', $this->file, $halaman));
        if (!empty($this->layer_method) && is_int($this->layer_method)) {
            $imagick->mergeImageLayers($this->layerMethod);
        }
        $imagick->setFormat($this->format);
        $output = $imagick;
        $imagick->clear();
        return $output;
    }
    
    public function run(int $page = null, string $output = '')
    {
        $this->set_page($page);
        if (!empty($output)) {
            if (is_dir($output)) {
               $this->_set_config('path', $output);
            }
        }
        $this->_output->$filename = null;
        $this->_output->$data = null;
        if ($this->count_page > 0) {
            if (!empty($page)) {
                if (!empty($output)) {
                    if (\is_dir($output)){
                        $filename = $this->_create_filename();
                    }else{
                        $filename = $output;
                    }
                }
                $data = $this->_convert();
                if (file_put_contents($filename, $data)) {
                    $this->_output->$filename = $filename;
                    $this->_output->$data = $data;
                }
            } else {
                $this->_output->$filename = [];
                $this->_output->$data = [];
                for ($i = 0; $i < $this->count_page; $i++) {
                    $this->set_page(($i + 1));
                    $filename = $this->_create_filename($i);
                    $data = $this->_convert();
                    if (file_put_contents($filename, $data)) {
                        $this->_output->$filename[$i] = $filename;
                        $this->_output->$data[$i] = $data;
                    }else{
                        $this->_output->$filename[$i] = null;
                        $this->_output->$data[$i] = null;
                    }
                }
            }
        }
        return $this;
    }

    private function _create_filename(int $indeks = 0) {
        $pdffilename = basename($this->file,".pdf");
        $filename = $this->_get_config('path', true).$pdffilename.'-'.$this->_get_config('prefix', true);
        if ($indeks > 0) {
            $filename .= $indeks;
        }
        $filename .= $this->_get_config('format', true);
        return $filename;
    }
    public function output() {
        return (object) \get_class_vars($this->output);
    }
}