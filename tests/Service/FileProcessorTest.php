<?php 

namespace App\tests\Service;

use App\Service\FileProcessor;
use Doctrine\Common\Annotations\Annotation\Enum;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class FileProcessorTest extends TestCase
{
    public function testCountFileLines()
    {
        $fileName = __DIR__ . '/docs/line-count.csv' ;
        $fileProcessor = new FileProcessor();
        $lineCount = $fileProcessor->getLinesCount($fileName);

        // assert that getLinesCount() method return the good number.
        $this->assertEquals(5, $lineCount);
    }


}