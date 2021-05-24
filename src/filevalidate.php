<?php
namespace Appkita\PDFtoImage;

use \Appkita\PDFtoImage\Exceptions\PdfDoesNotExist;

class FileValidate {
    private $method_remote_allow = [
        'https',
        'http',
        'ftp'
    ];
    private $auth = '';
    private $file_output = '';

    function __construct(string $file, string $username='', string $password='') {
        if (!empty($username)) {
            $this->auth = "$username:$password";
        }
        if (empty($file)) {
           throw new PdfDoesNotExist("You must set file pdf to convert"); 
        }
        
        if ($this->isFile($file)) {
            $this->file_output = $this->cekFile($file);
        } else {
            $this->file_output = $this->loadRemote($file);
        }
    }

    public function get() {
        return $this->file_output;
    }

    protected function cekFile(string $file) : string {
        $ext = \pathinfo($file, PATHINFO_EXTENSION);
        if ($ext != 'pdf') {
            throw new InvalidFormat("File `{$file}` not support. File must be pdf"); 
        }
        if (!\file_exists($file)) {
            throw new PdfDoesNotExist("File `{$file}` does not exist"); 
        }
        return $file;
    }

    protected function isFile(string $file) : bool {
        for($i = 0; $i < \sizeof($this->method_remote_allow); $i++) {
            if (\strpos($this->method_remote_allow[$i].':', $this->file)) {
                return false;
                break;
            }
        }
        return true;
    }

    protected function loadRemote(string $file) : string {
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
            return $output;
        }else{
            throw new PdfDoesNotExist("File `{$this->file}` does not exist"); 
        }
    }
}