<?php

namespace App\Command;

use App\Service\FileProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to import file in empty database.
 */
class RppsImport extends Command {

        // the name of the command (the part after "bin/console")
        protected static $defaultName = 'app:import-rpps-datas';
        private $entityManager;
        private $fileProcessor;

        public function __construct($projectDir, EntityManagerInterface $entityManager, FileProcessor $fileProcessor)
        {
            $this->projectDir = $projectDir;
            $this->entityManager = $entityManager;
            $this->fileProcessor = $fileProcessor;
            parent::__construct();
        }

        protected function configure()
        {
            $this->setDescription('Import RPPS File into databse')
                ->setHelp('This command will import a RPPS CSV file into your database.');
        }
    
        protected function execute(InputInterface $input, OutputInterface $output)
        {
            try {
                // Turning off doctrine default logs queries for saving memory
                $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

                // Showing when the cps process is launched
                $start = new \DateTime();
                $output->writeln('<comment>' . $start->format('d-m-Y G:i:s') . ' Start processing :---</comment>');

                /**
                 * Handling RPPS File
                 */
                $url="https://annuaire.sante.fr/web/site-pro/extractions-publiques?p_p_id=abonnementportlet_WAR_Inscriptionportlet_INSTANCE_gGMT6fhOPMYV&p_p_lifecycle=2&p_p_state=normal&p_p_mode=view&p_p_cacheability=cacheLevelPage&_abonnementportlet_WAR_Inscriptionportlet_INSTANCE_gGMT6fhOPMYV_nomFichier=ExtractionMonoTable_CAT18_ToutePopulation_202011241543.zip";
                $fileName ="ExtractionMonoTable_CAT18_ToutePopulation_202011241543";
                $input_rpps_file = $this->fileProcessor->getFile($this->projectDir, $url ,$fileName);

                $batchSize = 20;
                $lineCount = $this->fileProcessor->getLinesCount($input_rpps_file);

                $rpps = $this->fileProcessor->processRppsFile($output, $this->entityManager, $input_rpps_file, $lineCount, $batchSize);

                /**
                 * Handling CPS File
                 */
                $url="https://annuaire.sante.fr/web/site-pro/extractions-publiques?p_p_id=porteurportlet_WAR_Inscriptionportlet_INSTANCE_8W0X22j2B0ON&p_p_lifecycle=2&p_p_state=normal&p_p_mode=view&p_p_cacheability=cacheLevelPage&_porteurportlet_WAR_Inscriptionportlet_INSTANCE_8W0X22j2B0ON_nomFichier=Porteurs_CPS_CPF_202011241543.zip";
                $fileName ="Porteurs_CPS_CPF_202011241543";
                $input_cps_file = $this->fileProcessor->getFile($this->projectDir, $url ,$fileName);
                
                $batchSize = 20;
                $lineCount = $this->fileProcessor->getLinesCount($input_cps_file);
                
                $cps = $this->fileProcessor->processCpsFile($output, $this->entityManager, $input_cps_file, $lineCount, $batchSize);
            
                //Checking failure
                if (!$rpps == 0) {
                    echo 'Rpps load failed';
                    return Command::FAILURE;
                }

                if(!$cps == 0) {
                    echo 'Cps load failed';
                    return Command::FAILURE;
                }
                    
                return Command::SUCCESS;
                

            } catch(\Exception $e){

                error_log($e->getMessage());
                
            }
        }
}