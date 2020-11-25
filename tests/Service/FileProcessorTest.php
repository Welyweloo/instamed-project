<?php 

namespace App\Tests\Util;

use App\Service\FileProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class FileProcessorTest extends TestCase
{
    public function testGetFilePath()
    {
        $fileProcessor = new FileProcessor();

        var_dump(__DIR__);
        $fileName = 'newfile';
        $testPath = __DIR__ . $fileName;

        $fileNameWithService = $fileProcessor->getFilePath(__DIR__, $fileName);

        // assert that both path match perfectly!
        $this->assertEquals($testPath, $fileNameWithService);
    }
}