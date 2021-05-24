<?php
namespace Appkita\PDFtoImage;
use \Imagick;
use \Appkita\PDFtoImage\Config;
use \Appkita\PDFtoImage\FileValidate;
use \Appkita\PDFtoImage\Output;
use \Appkita\PDFtoImage\Exceptions\InvalidFormat;
use \Appkita\PDFtoImage\Exceptions\PdfDoesNotExist;
use \Appkita\PDFtoImage\Exceptions\PageDoesNotExist;


Class Convert {
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
        if (count($config) == count($config, COUNT_RECURSIVE)) {
            $cfg = $this->config();
            $val = [];
            foreach($cfg as $key => $value) {
                array_push($val, $key);
            }
            for($i = 0; $i < sizeof($config); $i++) {
                if (\sizeof($val) > $i) {
                    $this->{$val[$i]} = $config[$i];
                }
            }
        }else{
            foreach($config as $key => $value) {
                $this->{$key} = $value;
            }
        }
        return $this;
    }

    public function setFile($file) {
        $validate = new FileValidate($file);
        Config::$file = $validate->get();
        $imagick = new Imagick();
        $imagick->pingImage(Config::$file);
        Config::$count_page = $imagick->getNumberImages();
    }

    private function _configClass() {
        return new ReflectionClass('Config');
    }

    public function __get($name) {
        if (\property_exists('Config', $name)) {
           return $this->_configClass()->getStaticPropertyValue($name);
        }
    }

    public function __set($name, $value) {
        $name = \strtolower($name);
        if (\property_exists('Config', $name) && !\in_array($name, $this->not_change)) {
            if ($name == 'format') {
                if (!\in_array($value, $this->validOutputFormats)) {
                    throw new InvalidFormat("Format {$value} not support. follow this format ". \implode($this->validOutputFormats));
                }
            } else if ($name == 'path') {
                if (!\file_exists($value)) {
                    \mkdir($path, $mode);
                }
            }
            return $this->_configClass()->getProperty($name)->setValue($value);
        }
    }

    public function config() {
        return (object) \get_class_vars('Config');
    }

    private function _convert(string $file = '', array $config=[]) {
        if (!empty($file)) {
            $this->setFile($file);
        }
        if (\sizeof($config) > 0) {
            $this->initConfig($config);
        }
        $imagick = new Imagick();
        $imagick->setResolution(Config::$resolution, Config::$resolution);
        if (!empty(Config::$colorspace)){
            $imagick->setColorspace($this->colorspace);
        }
        if (!empty(Config::$quality)) {
            $imagick->setCompressionQuality(Config::$quality);
        }
        $imagick->readImage(sprintf('%s[%s]', Config::$file, $this->page - 1));
        if (!empty(Config::$layer_method) && is_int(Config::$layer_method)) {
            $imagick->mergeImageLayers($this->layerMethod);
        }
        $imagick->setFormat(Config::$format);
        return $imagick;
    }
    
    public function run(int $page = null, string $output = '')
    {
        if (!empty($page)) {
            if ($page <= Config::$count_page) {
                $this->page = $page;
            }
        }
        $filename =  Config::$prefix .'';
        if (!empty($output)) {
            if (is_dir($output)) {
                $this->path = $output;
            }
        }
        $pdffilename = basename(Config::$file,".pdf");
        $this->_output->$filename = null;
        $this->_output->$data = null;
        if (Config::$count_page > 0) {
            if (!empty($page)) {
                if ($page > Config::$count_page) {
                    $ttl = Config::$count_page;
                    throw new PageNotExists("Page `{$page}` not exist. Count page only {$ttl}");
                }
                if (!empty($output)) {
                    if (\is_dir($output)){
                        $filename = rtrim($output, '\/').DIRECTORY_SEPARATOR.$pdffilename.'-'.(!empty(Config::$prefix) ? Config::$prefix.'-' : '').$this->page.'.'.Config::$format;
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
                for ($i = 0; $i < Config::$count_page; $i++) {
                    $this->page += $i;
                    $filename = Config::$path.DIRECTORY_SEPARATOR.$pdffilename.'-'.(!empty(Config::$prefix) ? Config::$prefix.'-' : '').$this->page.'.'.Config::$format;
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