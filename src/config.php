<?php
namespace Appkita\PDFtoImage;
use \Imagick;
use \Appkita\PDFtoImage\FileValidate;

trait Config {
    protected $file = '';
    protected $format = 'png';
    protected $resolution = [
        'x'=>768,
        'y'=>1024
    ];
    protected $path = '';
    protected $prefix = 'convert';
    protected $layer_method = Imagick::LAYERMETHOD_FLATTEN;
    protected $colorspace;
    protected $quality;
    protected $count_page = 0;
    
    private $validOutputFormats = ['jpg', 'jpg', 'png'];
    private $not_change = ['count_page', 'file', 'validOutputFormats'];

    public function setFile($file) {
        $validate = new FileValidate($file);
        $this->file = $validate->get();
        $imagick = new Imagick();
        $imagick->pingImage($this->file);
        $this->count_page = $imagick->getNumberImages();
        $imagick->clear();
        return $this;
    }

    public function setResolution(int $x, int $y) {
        if ($x > 0) {
            $this->resolution['x'] = $x;
        }
        if ($y > 0) {
            $this->resolution['y'] = $y;
        }
        return $this;
    }

    private function _get_reflection_property(int $type = null) {
        $cfg = new \ReflectionClass($this);
        $property = $cfg->getProperties($type);
        $output = [];
        foreach($property as $key) {
            array_push($output, [$key->name, $this->{$key->name}]);
        }
        return $output;
    }

    protected function _get_config(string $key = null, bool $sufix=false) {
        $key = \strtolower($key);
        $property = $this->_get_reflection_property(\ReflectionProperty::IS_PROTECTED); 
        if (!empty($key)) {
            $val = null;
            if (!\in_array($key, $this->not_change)) {
                if ($key == 'format' && $sufix) {
                    $val = empty($this->format) ? '.png' : '.'.$this->format; 
                } else if ($key == 'path' && $sufix) {
                    $val =  !empty($this->path) ? $this->path.DIRECTORY_SEPARATOR : \dirname(\realpath($this->file)).DIRECTORY_SEPARATOR;
                }else if ($key == 'prefix' && $sufix) {
                    $val = !empty($this->prefix) ? $this->prefix.'-' : '';
                } else {
                    $val =  $property[$key];                         
                }
            }
            return $val;
        } else {
            return $property;
        }
    }

    private function _valdiate_config($key, $value) {
        switch(\strtolower($key)) {
            case 'format':
                if (!\in_array($value, $this->validOutputFormats)) {
                    throw new InvalidFormat("Format {$value} not support. follow this format ". \implode($this->validOutputFormats));
                }
                break;
            case 'path':
                if (!\file_exists($value)) {
                    \mkdir($value, $mode);
                }
                break;
        }
    }

    private function _set_config(string $key, $val) {
        if (!empty($key)) {
            if (!\in_array($key, $this->not_change)) {
                $this->_valdiate_config($key, $val);
                $this->{$key} = $val;
            }
        }
    }

    protected function initConfig(array $config) {
        if (\is_array($config)) {
            if (count($config) == count($config, COUNT_RECURSIVE)) {
                $cfg = \get_object_vars($this);
                $val = [];
                foreach($cfg as $key => $value) {
                    array_push($val, $key);
                }
                for($i = 0; $i < sizeof($config); $i++) {
                    if (\sizeof($val) > $i) {
                        $this->_set_config($val[$i],$config[$i]);
                    }
                }
            }else{
                foreach($config as $key => $value) {
                    $this->_set_config($key,$value);
                }
            }
        }
    }
}