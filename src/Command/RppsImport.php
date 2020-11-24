<?php

namespace App\Command;

use App\Entity\RPPS;
use Doctrine\ORM\EntityManagerInterface;
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
        private $entityManager;

        public function __construct($projectDir, EntityManagerInterface $entityManager)
        {
            $this->projectDir = $projectDir;
            $this->entityManager = $entityManager;
            parent::__construct();
        }

        protected function configure()
        {
            $this->setDescription('Import RPPS File into databse')
                ->setHelp('This command will import a RPPS CSV file into your database.')
                ->addArgument('rpps-file', InputArgument::REQUIRED, 'RPPS File name')
                ->addArgument('cps-file', InputArgument::REQUIRED, 'CPS File name');
        }
    
        protected function execute(InputInterface $input, OutputInterface $output)
        {

            // Showing when the script is launched
            $now = new \DateTime();
            $output->writeln('<comment>Start : ' . $now->format('d-m-Y G:i:s') . ' ---</comment>');

            //Recover input file absolute url
            $input_rpps_file = $this->projectDir . "/docs/" . $input->getArgument('rpps-file');
            $input_cps_file = $this->projectDir . "/docs/" . $input->getArgument('cps-file');

            // Turning off doctrine default logs queries for saving memory
            $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
            
            /** @var RPPSRepository rppsRepository */
            //$rppsRepository = $this->entityManager->getRepository(RPPS::class);
            

            //Parse rpps file line by line to transform them into array
            $batchSize = 20;
            $row = 1;

            if (($handle = fopen($input_rpps_file, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                    
                    $row++;

                    if ( $row != 1) {
                        $newRpps = new RPPS();
      
                        $newRpps->setIdRpps(intval($data[2]));
                        $newRpps->setTitle($data[4]);
                        $newRpps->setFirstName($data[5]);
                        $newRpps->setLastName($data[6]);
                        $newRpps->setSpecialty($data[8]);
                        $newRpps->setZipcode($data[31]);
                        $newRpps->setCity($data[30]);
                        $newRpps->setPhoneNumer("0". $data[30]);
                        $newRpps->setEmail($data[39]);
                        $newRpps->setFinessNumber($data[18]);
                        $this->entityManager->persist($newRpps);

                    }

                    // Each 20 users persisted we flush everything
                    if (($row % $batchSize) === 0) {
                        $this->entityManager->flush();
                        // Detaches all objects from Doctrine for memory save
                        $this->entityManager->clear();
                
                        $now = new \DateTime();
                        $output->writeln(' of users imported ... | ' . $now->format('d-m-Y G:i:s'));
                    }

  
                }

                fclose($handle);
            }


        }


}