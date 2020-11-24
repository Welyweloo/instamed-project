<?php 

namespace App\Service;

use App\Entity\RPPS;
use Symfony\Component\Console\Output\OutputInterface;

class FileProcessor
{
    public function getFilePath($projectDir, $argument): string
    {
        $filePath = $projectDir . "/docs/" . $argument;
        return $filePath;
    }

    public function getLinesCount($file): int
    {
        $linecount = 0;

        $handle = fopen($file, "r");
        while(!feof($handle)){
            $line = fgets($handle);
            $linecount++;
        }

        fclose($handle);

        return $linecount;
    }

    public function processRppsFile(OutputInterface $output, $entityManager, $file, $lineCount, $batchSize): int
    {
        // Showing when the script is launched
        $now = new \DateTime();
        $output->writeln('<comment>Start : ' . $now->format('d-m-Y G:i:s') . ' | You have '. $lineCount. ' lines to import from your RPPS file to your database ---</comment>');

        //Persist rpps datas in database 
        if (($handle = fopen($file, "r")) !== FALSE) {

            /** @var RPPSRepository rppsRepository */
            $rppsRepository = $entityManager->getRepository(RPPS::class);

            $row = 0;
            
            while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {

                if ( $row > 0) {
                    if (!$rppsRepository->findOneBy(["id_rpps" => $data[1]])) {
                        $newRpps = new RPPS();
    
                        $newRpps->setIdRpps($data[2]);
                        $newRpps->setTitle($data[4]);
                        $newRpps->setFirstName($data[5]);
                        $newRpps->setLastName($data[6]);
                        $newRpps->setSpecialty($data[8]);
                        $newRpps->setAddress($data[24] . " " . $data[25] . " " . $data[27] . " " . $data[28] . " " . $data[29]);
                        $newRpps->setZipcode($data[31]);
                        $newRpps->setCity($data[30]);
                        $newRpps->setPhoneNumber(str_replace(' ', '', $data[36]));
                        $newRpps->setEmail($data[39]);
                        $newRpps->setFinessNumber($data[18]);
                        
                        $entityManager->persist($newRpps);
                        $entityManager->flush();
                    }

                }

                // Each 20 lines persisted we flush everything
                if (($row % $batchSize) === 0) {
                    // Detaches all objects from Doctrine for memory save
                    $entityManager->clear();
            
                    $now = new \DateTime();
                    $output->writeln($row.' of lines imported out of ' . $lineCount . ' | ' . $now->format('d-m-Y G:i:s'));
                }

                $row++;

            }

            fclose($handle);
            $output->writeln('<comment>End : ' . $now->format('d-m-Y G:i:s') . ' | You have imported all datas from your RPPS file to your database ---</comment>');

        } 

        return 0;
    }

    public function processCpsFile(OutputInterface $output, $entityManager, $file, $lineCount, $batchSize): int
    {
        // Showing when the script is launched
        $now = new \DateTime();
        $output->writeln('<comment>Start : ' . $now->format('d-m-Y G:i:s') . ' | You have '. $lineCount. ' lines to go through on your CPS ---</comment>');

        //Persist cps datas in database 
        if (($handle = fopen($file, "r")) !== FALSE) {
            
            /** @var RPPSRepository rppsRepository */
            $rppsRepository = $entityManager->getRepository(RPPS::class);

            $row = 0;

            while (($data = fgetcsv($handle, 1000, "|")) !== FALSE) {

                if ($row > 0) {
                    if ($existingRpps = $rppsRepository->findOneBy(["id_rpps" => $data[1]])) {
                        $existingRpps->setCpsNumber($data[11]);
                        $this->entityManager->persist($existingRpps);
                        $this->entityManager->flush();
                    }
                }

                // Each 20 lines persisted we flush everything
                if (($row % $batchSize) === 0) {
                    // Detaches all objects from Doctrine for memory save
                    $this->entityManager->clear();
            
                    $now = new \DateTime();
                    $output->writeln($row.' of lines imported out of ' . $lineCount . ' | ' . $now->format('d-m-Y G:i:s'));
                }

                $row++;
            }

            fclose($handle);
            $output->writeln('<comment>End : ' . $now->format('d-m-Y G:i:s') . ' | You have imported all needed datas from your CPS file to your database ---</comment>');

        }

        return 0;
    }

}