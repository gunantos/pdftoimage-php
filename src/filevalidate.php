<?php
namespace Appkita\PDFtoImage;

use \Appkita\PDFtoImage\Exceptions\PdfDoesNotExist;
use \Appkita\PDFtoImage\Exceptions\InvalidFormat;
use \Appkita\PDFtoImage\Exceptions\UrlNotFound;

class FileValidate {
    private $auth = '';
    private $file_output = '';

    function __construct(string $file, string $username='', string $password='') {
        if (!empty($username)) {
            $this->auth = "$username:$password";
        }
        if (empty($file)) {
           throw new PdfDoesNotExist("You must set file pdf to convert"); 
        }
        
        if (\filter_var($file, FILTER_VALIDATE_URL)) {
            $this->file_output = $this->loadRemote($file);
        } else {
            $this->file_output = $this->cekFile($file);
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

    protected function loadRemote(string $file) : string {
        $output= __DIR__.DIRECTORY_SEPARATOR."temp".DIRECTORY_SEPARATOR."temp_download.pdf";
        $hasil = \fopen($output, "w");
        $options = array(
           CURLOPT_FILE  => $hasil,
           CURLOPT_TIMEOUT => 28800,
           CURLOPT_URL => $file,
           CURLOPT_SSL_VERIFYHOST => 0,
           CURLOPT_SSL_VERIFYPEER => 0

        );
        if (!empty($this->auth)) {
            $options[CURLOPT_USERPWD] = $this->auth;
        }
        $ch = \curl_init();
        curl_setopt_array($ch, $options);
        curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($responseCode == 200){
            return $output;
        }else{
            throw new UrlNotFound("File `{$this->file}` does not exist"); 
        }
    }
}