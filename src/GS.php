<?php
namespace Appkita\PDFtoImage;
use Appkita\PDFtoImage\IMAGE;
use Appkita\PDFtoImage\Exceptions\ErrorConfig;
use Appkita\PDFtoImage\Helpers;
class GS {
    const GS_CMD_WIN32 = 'gswin32c.exe';
    const GS_CMD_WIN64 = 'gswin64c.exe';
    const GS_CMD_LINUX = 'gs';
    const MAX_RESOLUTION = 300;
    const FORMAT_PNG = 'png';
    const FORMAT_JPEG = 'jpeg';

    private $OS = IMAGE::OS_WIN;
    private $GS_PATH = null;
    private $GS_CMD = null;
    private $GS_64 = false;
    private $GS_VERSION = null;

    function __construct() {
        $this->OS = Helpers::getOS();
        $this->init();
    }

    public function version() {
        return $this->GS_VERSION;
    }

    private function getpage($page) : array {
        $start = 1;
        $end = 0;
        if (\is_array($page)) {
           if (count($array) == count($array, COUNT_RECURSIVE)) 
            {
               $start = isset($page[0]) ? $page[0] : $start;
               $end = isset($page[1]) ? $page[1] : $end;
            }else{
               if (isset($page['start'])) {
                   $start= $page['start'];
               }else if (isset($page->start)) {
                   $start = $page->start;
               }
                if (isset($page['end'])) {
                   $end= $page['end'];
               }else if (isset($page->end)) {
                   $end = $page->end;
               }
            }
        } else {
            $start =  $page;
        }
        if (empty($start) || $start < 0) {
            $start = 1;
        }
        return ['start'=>$start, 'end'=>$end];
    }

    public function getNumberOfPages($pdf){
        if($this->GS_CMD == GS::GS_CMD_WIN32 || $this->GS_CMD == GS::GS_CMD_WIN64){
            $pdf = str_replace('\\', '/', $pdf);
        }
        $pages = $this->executeGS('-q -dNODISPLAY -c "('.$this->pdf.') (r) file runpdfbegin pdfpagecount = quit"',true);
        return intval($pages);
    }

    public function toIMG($pdf, $output, $ext = GS::FORMAT_PNG, $page = 0, $resolution = 300, $quality = 100, $prefix = 'conv', $pngScaleFactor = null  ){
        $page = $this->getpage($page);
        $ttl_page = $this->getNumberOfPages($pdf);
        if($page['start'] > $ttl_page) {
            $page['start'] = 1;
        }
        if ($page['end'] == 0 || $page['end'] > $ttl_page) {
            $page['end'] = $ttl_page;
        }

        if ($resolution > GS::MAX_RESOLUTION) {
            $resolution = GS::MAX_RESOLUTION;
        }
        if ($quality > 100 || $quality < 1) {
            $quality = 100;
        }
        $ext = trim(strtolower($ext));
        if ($ext != GS::FORMAT_JPEG && $ext != GS::FORMAT_PNG) {
            $ext = GS::FORMAT_PNG;
        }
        if ($ext == GS::FORMAT_JPEG) {
            $imageDeviceCommand = 'jpeg';
            $downscalefactor = !empty($pngScaleFactor) ? "-dDownScaleFactor=".$pngScaleFactor : "";
        } else {
            $imageDeviceCommand = 'png16m';
            $downscalefactor = '';
        }
        
        $image_path = $output."/".$prefix."%d.".$ext;
        $output = $this->executeGS("-dSAFER -dBATCH -dNOPAUSE -sDEVICE=".$imageDeviceCommand." ".$downscalefactor." -r".$resolution." -dNumRenderingThreads=4 -dFirstPage=".$page['start']." -dLastPage=".$page['end']." -o\"".$image_path."\" -dJPEGQ=".$quality." -q \"".($pdf)."\" -c quit");

        $fileArray = [];
        $tf =  $page['end'] - $page['start'];
        for($i=1; $i< ($page['end'] - $page['start']); ++$i){
            $fileArray[] = $prefix."$i.".$ext;
        }
        if(!$this->checkFilesExists($output, $fileArray)){
            $errrorinfo = implode(",", $output);
            throw new \Exception('PDF_CONVERSION_ERROR '.$errrorinfo);
        }
        return $fileArray;
    }

    public function toPDF(string $output, array $image) {
        $res = '';
        foreach($image as $img) {
            if ($this->GS_CMD == GS::GS_CMD_WIN32 || $this->GS_CMD == GS::GS_CMD_WIN64) {
                $img = \str_replace('\\', '/', $img);
            }
            $res .= '('. $img.') viewJPEG showpage';
        }
        $psfile  = $this->getGSLibFilePath('viewjpeg.ps');
        $command = '-dBATCH -dNOPAUSE -sDEVICE=pdfwrite -o"'.$output.'" "'.$psfile .'" -c "'.$res.'"';
        $command_results = $this->executeGS($command);
        if(!$this->checkFilesExists("",[$output])){
            throw new \Exception("Unable to make PDF : ".$command_results[2],500);
        }
        return $output;
    }

    public function execute($command, $is_shell = false) {
        return $this->_execute($this->GS_CMD.' '. $command, $is_shell);
    }

    protected function init() {
        if (empty($this->PATH)) {
            if ($this->OS = IMAGE::OS_WIN) {
                if (trim($gs_bin_path = $this->_execute('where '. GS::GS_CMD_WIN64, true))) {
                    $this->GS_64 = true;
                    $this->GS_CMD = 'gswin64c.exe';
                    $this->GS_PATH = trim(\str_replace("bin\\". $this->GS_CMD, "", $gs_bin_path));
                } else  if (trim($gs_bin_path = $this->_execute('where '. GS::GS_CMD_WIN32, true))) {
                    $this->GS_64 = false;
                    $this->GS_CMD = 'gswin32c.exe';
                    $this->GS_PATH = trim(\str_replace("bin\\". $this->GS_CMD, "", $gs_bin_path));
                } else {
                    throw ErroConfig::forNotSupportLibrary('Ghostscript');
                }
                $output =  $this->_execute($this->GS_CMD.' --version 2>&1');
                $this->GS_VERSION =  doubleval($output[0]);
            } else {
                $output = $this->_execute('gs --version 2>&1');
                if(!((is_array($output) && (strpos($output[0], 'is not recognized as an internal or external command') !== false)) || !is_array($output) && trim($output) == "")){
                    $this->GS_CMD = GS::GS_LINUX;
                    $this->GS_VERSION = doubleval($output[0]);
                    $this->GS_PATH = ""; // The ghostscript will find the path itself
                    $this->GS_64 = false;
                } else {
                    throw ErroConfig::forNotSupportLibrary('Ghostscript');
                }
            }
        }
    }

    protected function _execute($command, $is_shell = false) {
        $output = null;
        if ($is_shell) {
            $output = \shell_exec($command);
        } else {
            exec($command, $output);
        }
        return $output;
    }

    public function getGSLibFilePath($filename) {
        if (empty($this->GS_PATH)) {
            return $filename;
        }
        if ($this->OS == IMAGE::OS_WIN) {
            return $this->GS_PATH.'\\lib\\'. $filename;
        } else {
            return $this->GS_PATH.'/lib/'. $filename;
        }
    }
}