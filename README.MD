# PDF TO IMAGE

[![PHP Composer](https://github.com/gunantos/pdftoimage-php/actions/workflows/php.yml/badge.svg)](https://github.com/gunantos/pdftoimage-php/actions/workflows/php.yml)
![Discord](https://img.shields.io/discord/846036920811126844?style=plastic)
![Travis (.org)](https://img.shields.io/travis/gunantos/pdftoimage-php?style=plastic)
![GitHub branch checks state](https://img.shields.io/github/checks-status/gunantos/pdftoimage-php/main?style=plastic)
![Bitbucket Pipelines](https://img.shields.io/bitbucket/pipelines/andtho89/pdftoimage-php/main)<br>
<a href="https://sponsor.app-kita.net" target="_blank"><img src="https://img.shields.io/github/sponsors/gunantos?logo=gunantos&style=for-the-badge" title="Pay Coffe" /></a><br>
Pdf to image library using Imagick<br>

Installation

- composer
  `composer install appkita/pdftoimage`
- download [Github](https://github.com/gunantos/pdftoimage-php/releases)

## NEW Version 3.0 (BETA)

- add convert using ghostscript

### INSTALLATION EXTENSION

1. Windows
   a. Ghostscript
   download from [ghostscript](https://www.ghostscript.com/download/gsdnld.html)
   b. Imagick

   - Check PHP Version `php -i|find "PHP Version"`
   - Check PHP is Thread Safety `php -i|find "Thread Safety"`
   - Check PHP Architecture `php -i|find "Architecture"`
   - Download imagick from [Imagick](https://pecl.php.net/package/imagick)
   - Once you downloaded the correct files:
     - Extract from `php_imagick-….zip` the `php_imagick.dll `file, and save it to the ext directory of your PHP installation
     - Extract from `ImageMagick-….zip` the DLL files located in the bin folder that start with `CORE_RL` or `IM_MOD_RL`, and save them to the PHP root directory `(where you have php.exe)`, or to a directory in your PATH variable
     - Add this line to your `php.ini` file: `extension=php_imagick.dll`
     - Restart the Apache/NGINX Windows service (if applicable)
   - To test if the extension works, you can run this PHP code:
     ```php
     <?php
     $image = new Imagick();
     $image->newImage(1, 1, new ImagickPixel('#ffffff'));
     $image->setImageFormat('png');
     $pngData = $image->getImagesBlob();
     echo strpos($pngData, "\x89PNG\r\n\x1a\n") === 0 ? 'Ok' : 'Failed';
     ```

2. Linux

   - Ghostscript
     download from [ghostscript](https://www.ghostscript.com/download/gsdnld.html)
   - Imagick

     - Centos
       ```sh
         sudo yum install php-imagick
         php -m | grep imagick
       ```
     - Debian

     ```sh
       sudo apt-get install php-imagick
       php -m | grep imagick
     ```

### Use

```php
 $file = '<<pdf file>>';
 //or
 $file = 'URL FILE';
 //configuration
 $option = [
   'path' => '<<result path>>', //default empty
   'format' => 'png', //set image format output
   'resolution' => 'set resultion image',
   'prefix'=> 'convert', //set prefix name image
   'layer_method'=>Imagick::LAYERMETHOD_FLATTEN, //follow Imagick Layer Method
   'colosrpace'=> null, //follow Imagick colorspace
   'quality'=> null, //follow imagick result quality
 ];
 $spesific_page = null;
 $convert = new  \Appkita\PDFtoImage\Convert($file, $option);
 $result = $convert->run(); //set null if you wont
  //or
  $result = $convert->run($spesific_page);

 /**
  * Or
  */
  $convert = new \Appkita\PDFtoImage\Convert($file);
  $convert->path = '<<your path>>';
  $convert->format = 'jpg';
  $convert->run($spesific_page);
 /**
  * Or
  */
  $convert = new \Appkita\PDFtoImage\Convert();
  $result = $convert->setFile($filepdf)->run($spesific_page, $options); // if is_string $options return $path output
```

2. URL

### Lear More

- [php](https://www.php.net/manual/en)
- [Imagick](https://www.php.net/manual/en/book.imagick.php)'
- [Ghostscript](https://www.ghostscript.com/download/gsdnld.html)

### Chat

[Discord](https://discord.gg/bXUWCSaw)

### Sponsor

[Pay Coffe](https://sponsor.app-kita.net)
