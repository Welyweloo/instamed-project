<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;



class RppsImport extends Command {

        // the name of the command (the part after "bin/console")
        protected static $defaultName = 'app:import-rpps-datas';

        public function __construct($projectDir)
        {
            $this->projectDir = $projectDir;
            parent::__construct();
        }

        protected function configure()
        {
            $this->setDescription('Import RPPS File into databse')
                ->setHelp('This command will import a RPPS CSV file into your database.')
                ->addArgument('file', InputArgument::REQUIRED, 'RPPS File name');
        }
    
        protected function execute(InputInterface $input, OutputInterface $output)
        {
            //Convert CSV file into interable 
            $inputFile = $this->projectDir . "/docs/" . $input->getArgument('file');
            
            $decoder = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
            $rows = $decoder->decode(file_get_contents($inputFile), 'csv');
            dd($rows);
            

            //Loop over records

                //Create new records 
        }


}