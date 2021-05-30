<?php
namespace Appkita\PDFtoImage;
use \Imagick;
use \Appkita\PDFtoImage\Config;
use \Appkita\PDFtoImage\FileValidate;
use \Appkita\PDFtoImage\Output;
use \Appkita\PDFtoImage\Exceptions\InvalidFormat;
use \Appkita\PDFtoImage\Exceptions\PdfDoesNotExist;
use \Appkita\PDFtoImage\Exceptions\PageDoesNotExist;
use Appkita\PDFtoImage\Config;

Class Convert {
    use Config;

    private $page = 1;
    protected $validOutputFormats = ['jpg', 'jpg', 'png'];
    private $not_change = ['count_page', 'file'];
    private $_output;

    function __construct(string $file='', array $config=[]) {
        if (!empty($file)) {
            $this->setFile($file);
        }
        $this->_output = new Output();
        $this->initConfig($config);
    }

    public function initConfig(array $config) {
        if (count($config) != count($config, COUNT_RECURSIVE)) {
            foreach($config as $key => $value) {
                if (isset($this->{$key}) && $key != 'file') {
                    $this->{$key} = $value;
                }
            }
        }
        return $this;
    }

    public function setFile($file) {
        $validate = new FileValidate($file);
        $this->file = $validate->get();
        $imagick = new Imagick();
        $imagick->pingImage($this->file);
        $this->count_page = $imagick->getNumberImages();
    }

    public function __set($name, $value) {
        $name = \strtolower($name);
        if (!\in_array($name)) {
            if (isset($this->{$name})) {
                return $this->{$name} = $value;
            }
        }
    }

    public function config() {
        return (object) \get_class_vars('Config');
    }

    public function setResolution(int $height, int $width) {
        $this->width = $width;
        $this->height = $height;
    }

    private function _convert(string $file = '', array $config=[]) {
        if (!empty($file)) {
            $this->setFile($file);
        }
        if (\sizeof($config) > 0) {
            $this->initConfig($config);
        }
        $imagick = new Imagick();
        $imagick->setResolution($this->width, $this->height);
        if (!empty($this->colorspace)){
            $imagick->setColorspace($this->colorspace);
        }
        if (!empty($this->quality)) {
            $imagick->setCompressionQuality($this->quality);
        }
        $imagick->readImage(sprintf('%s[%s]', $this->file, $this->page - 1));
        if (!empty($this->layer_method) && is_int($this->layer_method)) {
            $imagick->mergeImageLayers($this->layerMethod);
        }
        $imagick->setFormat($this->format);
        return $imagick;
    }
    
    public function run(int $page = null, string $output = '')
    {
        if (!empty($page)) {
            if ($page <= $this->count_page) {
                $this->page = $page;
            }
        }
        $filename =  $this->prefix .'';
        if (!empty($output)) {
            if (is_dir($output)) {
                $this->path = $output;
            }
        }
        $pdffilename = basename($this->file,".pdf");
        $this->_output->$filename = null;
        $this->_output->$data = null;
        if ($this->count_page > 0) {
            if (!empty($page)) {
                if ($page > $this->count_page) {
                    $ttl = $this->count_page;
                    throw new PageNotExists("Page `{$page}` not exist. Count page only {$ttl}");
                }
                if (!empty($output)) {
                    if (\is_dir($output)){
                        $filename = rtrim($output, '\/').DIRECTORY_SEPARATOR.$pdffilename.'-'.(!empty($this->prefix) ? $this->prefix.'-' : '').$this->page.'.'.$this->format;
                    }else{
                        $filename = $output;
                    }
                }
                $data = $this->_convert();
                if (file_put_contents($filename, $data)) {
                    $this->_output->$filename = $filename;
                    $this->_output->$data = $this->_convert();
                }
            } else {
                $this->_output->$filename = [];
                $this->_output->$data = [];
                for ($i = 0; $i < $this->count_page; $i++) {
                    $this->page += $i;
                    $filename = $this->path.DIRECTORY_SEPARATOR.$pdffilename.'-'.(!empty($this->prefix) ? $this->prefix.'-' : '').$this->page.'.'.$this->format;
                    $data = $this->_convert();
                    if (file_put_contents($filename, $data)) {
                        $this->_output->$filename[$i] = $filename;
                        $this->_output->$data[$i] = $this->_convert($filename);
                    }else{
                        $this->_output->$filename[$i] = null;
                        $this->_output->$data[$i] = null;
                    }
                }
            }
        }
        return $this;
    }

    public function output() {
        return (object) \get_class_vars('Output');
    }
}