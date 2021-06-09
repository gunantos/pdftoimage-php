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
        $pages = $this->execute('-q -dNOSAFER -dNODISPLAY -c "('.$pdf.') (r) file runpdfbegin pdfpagecount = quit" -f '. $pdf ,true);
        return intval($pages);
    }

    public function toIMG($pdf, string $output, $ext = GS::FORMAT_PNG, $page = 0, $resolution = 300, $quality = 100, $prefix = 'page', $pngScaleFactor = null  ){
        if (!\file_exists($pdf)) {
            throw new PdfDoesNotExist($pdf);
        }
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
        $nama_file = \basename($pdf, '.pdf');
        
        $pathname =str_replace(' ', '_', $nama_file);
        $pathname = preg_replace('/[^A-Za-z0-9\_]/', '', $pathname);
        $output = \rtrim($output, '\\');
        $output = \rtrim($output, '/');
        

        $image_path = $output.DIRECTORY_SEPARATOR.$pathname;
        if (!\file_exists($image_path)) {
            @mkdir($image_path, 0777);
        }
        $output_name = $image_path;
        if (!empty($ext)) {
            $output_name = $prefix .'-';
        }
        $output_name .= '%d.'. $ext;
        $cmd = "-dNOSAFER -dBATCH -dNOPAUSE -sDEVICE=".$imageDeviceCommand." ".$downscalefactor." -r".$resolution." -dNumRenderingThreads=4 -dFirstPage=".$page['start']." -dLastPage=".$page['end']." -o\"".$output_name."\" -dJPEGQ=".$quality." -q \"".($pdf)."\" -c quit";
        $run = $this->execute($cmd);

        $fileArray = [];
        for($i=0; $i< ($page['end'] - $page['start']); ++$i){
            $fn = $image_path;
            if (!empty($prefix)) {
                $fn .= '-'. $prefix;
            }
            $fn .= '-'. ($i + 1) .'.'. $ext;
            if (!\file_exists($fn)) {
                $fileArray[] = $fn;
            }
        }
       # if(!Helpers::isFileExistPath($image_path, $fileArray)){
       #     $errrorinfo = implode(",", $fileArray);
       #     throw new \Exception('PDF_CONVERSION_ERROR '.$errrorinfo);
       # }
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
        $command_results = $this->execute($command);
        if(!$this->checkFilesExists("",[$output])){
            throw new \Exception("Unable to make PDF : ".$command_results[2],500);
        }
        return $output;
    }

    public function execute($command, $is_shell = false) {
        return $this->_execute($this->GS_CMD.' '. $command, $is_shell);
    }

    public function init() {
        if (empty($this->PATH)) {
            if ($this->OS = IMAGE::OS_WIN) {
                if (trim($gs_bin_path = $this->_execute('where '. GS::GS_CMD_WIN64, true))) {
                    $this->GS_64 = true;
                    $this->GS_CMD = GS::GS_CMD_WIN64;
                    $this->GS_PATH = trim(\str_replace("bin\\". $this->GS_CMD, "", $gs_bin_path));
                } else  if (trim($gs_bin_path = $this->_execute('where '. GS::GS_CMD_WIN32, true))) {
                    $this->GS_64 = false;
                    $this->GS_CMD = GS::GS_CMD_WIN32;
                    $this->GS_PATH = trim(\str_replace("bin\\". $this->GS_CMD, "", $gs_bin_path));
                } else {
                    throw ErrorConfig::forNotSupportLibrary('Ghostscript');
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