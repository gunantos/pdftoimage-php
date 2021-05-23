<?php
namespace Appkita\PDFtoImage;

use \Appkita\PDFtoImage\Exceptions\PdfDoesNotExist;

class FileValidate {
    private string $file = '';
    private array $method_remote_allow = [
        'https',
        'http',
        'ftp'
    ];
    private $auth = '';
    private $is_file = true;
    private $file_output = '';

    function __construct(string $file, string $username='', string $password='') {
        $this->file = $file;
        if (!empty($username)) {
            $this->auth = "$username:$password";
        }
        if ($this->isFile($this->file)) {
            return $this->cekFile();
        } else {
            return $this->loadRemote();
        }
    }

    public function get() {
        return $this->file_output;
    }

    protected function cekFile(string $file = '') : string {
         if (!empty($file)) {
            $this->file = $file;
        }
        if (!\file_exists($this->file)) {
                throw new PdfDoesNotExist("File `{$file}` does not exist"); 
        }
        $this->file_output = $this->file;
        return $this->file_output;
    }

    protected function isFile(string $file='') : bool {
        if (!empty($file)) {
            $this->file = $file;
        }
        for($i = 0; $i < \sizeof($this->method_remote_allow); $i++) {
            if (\strpos($this->method_remote_allow[$i].':', $this->file)) {
                $this->is_file = false;
                break;
            }
        }
        return $this->is_file;
    }

    protected function loadRemote(string $file = '') : string {
        if (!empty($file)) {
            $this->file = $file;
        }
        $output= __DIR__.DIRECTORY_SEPARATOR."temp".DIRECTORY_SEPARATOR."temp_download.pdf";
        $file = \fopen($output, "w");
        $ch = curl_init($this->file);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_FILE, $file);
        if (!empty($this->auth)) {
            curl_setopt($curl, CURLOPT_USERPWD, $this->auth);
        }
        curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($responseCode == 200){
            $this->file_output = $output;
            return $this->file_output;
        }else{
            throw new PdfDoesNotExist("File `{$this->file}` does not exist"); 
        }
    }
}