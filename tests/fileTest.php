<?php
use \Appkita\PDFtoImage\Convert;
final class PDFtoImageTest extends PHPUnit\Framework\TestCase
{
    public function testFileConvert() {
        $ttl_page = 3;
        $file = __DIR__.DIRECTORY_SEPARATOR .'test.pdf';
        $convert = new Convert($file);
        $result = $convert->run();
        $this->assertEquals($ttl_page, $result->config()->count_page);
    }
}

