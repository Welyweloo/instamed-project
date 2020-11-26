<?php

namespace App\Command;

use App\Entity\RPPS;
use App\Service\FileProcessor;
use App\Repository\RPPSRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateRPPSCommand extends Command
{
    protected static $defaultName = 'app:rpps:update';
    private $entityManager;

    public function __construct($projectDir, EntityManagerInterface $entityManager, FileProcessor $fileProcessor )
    {
        $this->projectDir = $projectDir;
        $this->entityManager = $entityManager;
        $this->fileProcessor = $fileProcessor;
        
        parent::__construct();
    }

    protected function configure()
    {
        $this
            //->setName('app:rpps:update')
            ->setDescription('Update RPPS Data in the database')
            ->addArgument('rpps-file', InputArgument::REQUIRED, 'RPPS File name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $input_rpps_file = $this->fileProcessor->getFilePath($this->projectDir, $input->getArgument('rpps-file'));
        $batchSize = 20;
        $lineCount = $this->fileProcessor->getLinesCount($input_rpps_file);

        $rpps = $this->fileProcessor->updateRppsFile($output, $this->entityManager, $input_rpps_file, $lineCount, $batchSize);





        /*$rppsRepository = $this->entityManager->getRepository(RPPS::class);

        $rppsDatas = $rppsRepository->findAll();
        foreach($rppsDatas as $rppsData) {
            var_dump($rppsData->getIdRpps());
            
        }*/

        return 0;

    }

    // public function getFilePath($projectDir, $argument): string
    // {
    //     //Check 'config/services.yaml
    //     $filePath = $projectDir . "/docs/" . $argument;
        
    //     return $filePath;
    // }
}