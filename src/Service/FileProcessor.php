<?php

namespace App\Service;

use App\Entity\RPPS;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use \ZipArchive;

/**
 * Contains all useful methods to process files and import them into database.
 */
class FileProcessor
{

    /**
     * Counts how much line there is in a file.
     *
     * @param [string] $file
     * The path of the file we want to process.
     * 
     * @return integer
     * The number of lines in a file.
     */
    public function getLinesCount($file): int
    {
        $linecount = 0;

        // Will go through file by iterating on each line to save memory
        $handle = fopen($file, "r");
        while (!feof($handle)) {
            $line = fgets($handle);
            $linecount++;
        }

        fclose($handle);

        return $linecount - 1;
    }

    /**
     * Parses a CSV file with ";" separator into a PHP array
     * and persistsAdnFlushes them into the database.  
     *
     * @param OutputInterface $output
     * The output instance used to display message to the user.
     * 
     * @param [object] $entityManager
     * The entity manager is a doctrince instance that allows us to 
     * persist and flush datas into database.
     * 
     * @param [string] $file
     * The path of the file to be processed.
     * 
     * @param [int] $lineCount
     * The amount of lines in the file.
     * 
     * @param [int] $batchSize
     * The amount of data to pass before emptying doctrice cache
     * 
     * @return integer
     * Returns 0 if the whole process worked.
     */
    public function processRppsFile(OutputInterface $output, $entityManager, $file, $lineCount, $batchSize): int
    {

        // Showing when the rpps process is launched
        $start = new \DateTime();
        $output->writeln('<comment>Start : ' . $start->format('d-m-Y G:i:s') . ' | You have ' . $lineCount . ' lines to import from your RPPS file to your database ---</comment>');

        // Will go through file by iterating on each line to save memory 
        if (($handle = fopen($file, "r")) !== FALSE) {

            /** @var RPPSRepository rppsRepository */
            $rppsRepository = $entityManager->getRepository(RPPS::class);

            $row = 0;

            while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {

                if ($row > 0) { //Exits header of csv file
                    if (!$rppsRepository->findOneBy(["id_rpps" => $data[1]])) { //Only persisting data if it's no a duplicate of previously created datas

                        //Creating an RPPS instance to set all datas 
                        //as we're going through each line, then
                        //persistAndFlush
                        
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

                //Used to save some memory out of Doctrine every 20 lines
                if (($row % $batchSize) === 0) {
                    // Detaches all objects from Doctrine for memory save
                    $entityManager->clear();

                    // Showing progression of the process
                    $end = new \DateTime();
                    $output->writeln($row . ' of lines imported out of ' . $lineCount . ' | ' . $end->format('d-m-Y G:i:s'));
                }

                $row++;
            }

            fclose($handle);

            //Creates a file timestamp to save the timestamp's file RPPS
            //fopen($projectDir . "/docs/" . "old-timestamp.txt", "w");
            
            //fwrite($myfile, $old_timestamp);
            
            //fclose($myfile);

            // Showing when the rpps process is done
            $output->writeln('<comment>End of loading : (Started at ' . $start->format('d-m-Y G:i:s') . ' / Ended at ' . $end->format('d-m-Y G:i:s') . ' | You have imported all datas from your RPPS file to your database ---</comment>');
            
            //$old_timestamp = filemtime($file);
           //$output->writeln($old_timestamp);
        }

        return 0;
    }

    /**
     * Parses a CSV file with "|" separator into a PHP array
     * and check existing datas in database, to match an additionnal data.  
     *
     * @param OutputInterface $output
     * The output instance used to display message to the user.
     * 
     * @param [object] $entityManager
     * The entity manager is a doctrince instance that allows us to 
     * persist and flush datas into database.
     * 
     * @param [string] $file
     * The path of the file to be processed.
     * 
     * @param [int] $lineCount
     * The amount of lines in the file.
     * 
     * @param [int] $batchSize
     * The amount of data to pass before emptying doctrice cache
     * 
     * @return integer
     * Returns 0 if the whole process worked.
     */
    public function processCpsFile(OutputInterface $output, $entityManager, $file, $lineCount, $batchSize): int
    {
        // Showing when the cps process is launched
        $start = new \DateTime();
        $output->writeln('<comment>Start : ' . $start->format('d-m-Y G:i:s') . ' | You have ' . $lineCount . ' lines to go through on your CPS ---</comment>');

        // Will go through file by iterating on each line to save memory
        if (($handle = fopen($file, "r")) !== FALSE) {

            /** @var RPPSRepository rppsRepository */
            $rppsRepository = $entityManager->getRepository(RPPS::class);

            $row = 0;

            while (($data = fgetcsv($handle, 1000, "|")) !== FALSE) {

                if ($row > 0) { //Exits header of csv file

                    //Checking if there is a match on the rpps number on both file  
                    //if so, we set the CPS number to the matching line, then
                    //persistAndFlush
                    if ($existingRpps = $rppsRepository->findOneBy(["id_rpps" => $data[1]])) {
                        $existingRpps->setCpsNumber($data[11]);
                        $entityManager->persist($existingRpps);
                        $entityManager->flush();
                    }
                }

                //Used to save some memory out of Doctrine every 20 lines
                if (($row % $batchSize) === 0) {
                    // Detaches all objects from Doctrine for memory save
                    $entityManager->clear();

                    // Showing progression of the process
                    $end = new \DateTime();
                    $output->writeln($row . ' of lines imported out of ' . $lineCount . ' | ' . $end->format('d-m-Y G:i:s'));
                }

                $row++;
            }

            fclose($handle);

            // Showing when the cps process is done
            $output->writeln('<comment>End of loading :  (Started at ' . $start->format('d-m-Y G:i:s') . ' / Ended at ' . $end->format('d-m-Y G:i:s') . ' | You have imported all needed datas from your CPS file to your database ---</comment>');
        
        }

        return 0;
    }

    
/**
 * Downloads zipfile from url, extracts files.
 *
 * @param [string] $projectDir
 * The root path of the project dir from current file.
 * 
 * @param [string] $url
 * The url from which we can recover the file
 * 
 * @param [string] $filename
 * The name of the file we want to process
 * 
 * @return string
 */
    public function getFile($projectDir, $url)
    {

        $filePath = $projectDir.'/docs/file.zip';
        $my_file = fopen($filePath, 'w');

        // initialisation of the session
        $ch = curl_init($url);

        // Change execution time 
        $timeout = 300;

        // configuration of options
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FILE, $my_file);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 4096);

        curl_exec($ch) OR die("Error in curl_exec()");

        echo("Zip downloaded.");

        fclose($my_file);
        curl_close($ch);


        $zip = new \ZipArchive;
 
        $res = $zip->open($filePath);
        echo 'après';
        $zip->extractTo($projectDir.'/docs/');
        $fileName = $projectDir . '/docs/' . $zip->getNameIndex(0);
        $zip->close();
        
        // Delete zip
        unlink($filePath);
 
        return $fileName;      
  
    }
    public function updateRppsFile(OutputInterface $output, $entityManager, $file, $lineCount, $batchSize): int
    {
        /**
         * knonwing if the file has been modified :
         * True : Start the script
         * otherwise, block the script
         * 
         */

         //Retrieves old timestamp from file
         //To check if RPPS file have been updated
        //  if (($handle = fopen($file_old_timestamp, "r")) !== FALSE) {

        //     /** @var RPPSRepository rppsRepository */
        //     $rppsRepository = $entityManager->getRepository(RPPS::class);

        //     $row = 0;

        //     while (($data = fgetcsv($handle, 1000, "|")) !== FALSE) {
                
        //         $old_timestamp = date("F d Y H:i:s", ($data[0]));
                
        //         $row++;
        //     }
        // }
        
        // $new_timestamp = filemtime($file);

        // //defines whether or not we need to update the database
        // if(date("F d Y H:i:s.", $new_timestamp) > date("F d Y H:i:s.", $old_timestamp))
        // {
        //     $modified = true;
        //     $old_timestamp = $new_timestamp;
        // }
        // else
        // {
        //     $modified = false;
            
        // }

        // Showing when the rpps process is launched
        $start = new \DateTime();
        $output->writeln('<comment>Start : ' . $start->format('d-m-Y G:i:s') . ' | You have ' . $lineCount . ' lines to import from your RPPS file to your database ---</comment>');


        /** @var RPPSRepository rppsRepository */
        $rppsRepository = $entityManager->getRepository(RPPS::class);

        $rppsDatas = $rppsRepository->findAll();

        //if($modified) // If the timestamp's file has been modified
        //{

            // Will go through file by iterating on each line to save memory 
            if (($handle = fopen($file, "r")) !== FALSE) {

                $row = 0;

                while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {

                    if ($row > 0) { //Exits header of csv file

                        if (!$rppsRepository->findOneBy(["id_rpps" => $data[2]])) { //Only persisting data if it's no a duplicate of previously created datas
                        
                            $output->writeln("New data to insert into the database");
                            //$output->writeln("New PP ID : " . $data[2]);
                            //$output->writeln($old_timestamp);

                            //Creating an RPPS instance to set all datas 
                            //as we're going through each line, then
                            //persistAndFlush
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
                        elseif(!$rppsRepository->findOneBy(["id_rpps" => $data[2]]))
                        {
                            $output->writeln("This data doesn't exist in the RPPS file : need to delete into the database");
                            //$output->writeln($data[2]);
                        }
                        else
                        {
                            $output->writeln("Data already exists");
                        }

                    }

                    //Used to save some memory out of Doctrine every 20 lines
                    if (($row % $batchSize) === 0) {
                        // Detaches all objects from Doctrine for memory save
                        $entityManager->clear();

                        // Showing progression of the process
                        $end = new \DateTime();
                        $output->writeln($row . ' of lines imported out of ' . $lineCount . ' | ' . $end->format('d-m-Y G:i:s'));
                    }

                    $row++;
                }

                fclose($handle);

                //Delete the file
                //unlink($file);

                // Showing when the rpps process is done
                $output->writeln('<comment>End of loading : (Started at ' . $start->format('d-m-Y G:i:s') . ' / Ended at ' . $end->format('d-m-Y G:i:s') . ' | You have imported all datas from your RPPS file to your database ---</comment>');
            }

        // }
        // else
        // {
        //     $output->writeln($new_timestamp);
        //     $output->writeln("The file has not been modified");
        // }

        return 0;
    }

}
