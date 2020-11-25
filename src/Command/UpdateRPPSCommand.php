<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateRPPSCommand extends Command
{
    protected static $defaultName = 'UpdateRPPS';

    protected function configure()
    {
        $this
            ->setName('app:rpps:update')
            ->setDescription('Update RPPS Data in the database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $data = $this->importCsvToArray($file);

        if($modified == true){

            $table_rpps = execute("SELECT id FROM RPPS");

        
            foreach ($data as $d) {

                foreach($table_rpps as $id_rpps){

                    $d = $this->parseData($d);

                    try {

                        if($d[0]!= $table_rpps)
                        {
                        
                            $this->getConnection()->executeQuery("INSERT INTO RPPS (number, title,first_name,last_name,speciality,address,postal_code,city,phone_number,finess) VALUES (:number, :title,:firstName,:lastName,:speciality,:address,:postalCode,:city,:phoneNumber,:finess)",$d);
                        }
                        
                        $output->writeln("Importing {$d['firstName']} {$d['lastName']}");

                    }catch (\Exception $exception) {

                        $output->writeln($exception->getMessage());
                    }

                }
            }
        }
        else
        {
            $output->writeln("The RPPS file has not been modified.");
        }
    }

    /**
     * Check if the file has been modified :
     * 
     * True : Start the script
     * otherwise, block the script
     * 
     */


    public function isModified(int $old_timestamp, $file)
    {
        $old_timestamp;
        
        $new_timestamp = filemtime($file);

        if($new_timestamp != $old_timestamp)
        {
            //We load the new timestamp in the $old_timestamp.
            $old_timestamp = $new_timestamp;
            
            //this variable will allow the script to start the execution
            $modified = true;

        }
        else
        {
            //On false, the script is not executed.
            $modified = false;
        }

        return $modified;

    }

    /**
     * @param array $data
     * @return array
     */
    public function parseData(array $data) : array
    {


        $postalCode = $data['Code postal (coord. structure)'];

        $city = $data['Libellé commune (coord. structure)'];

        if(!$city) {
            $city = trim(str_replace($postalCode,"",$data["Bureau cedex (coord. structure)"]));
        }

        $d =  array(
            'number' => preg_replace("#^0+#","",$data['Identifiant PP']),
            'title' => $data['Libellé civilité'],
            'firstName' => $data["Prénom d'exercice"],
            'lastName' => $data["Nom d'exercice"],
            'speciality' => $data["Libellé profession"],
            'address' => trim("{$data['Numéro Voie (coord. structure)']} {$data['Libellé type de voie (coord. structure)']} {$data['Libellé Voie (coord. structure)']}"),
            'postalCode' => $postalCode,
            'city' => $city,
            'phoneNumber' => $data['Téléphone (coord. structure)'],
            'finess' => $data["Numéro FINESS établissement juridique"],
        );


        try {
            $d['phoneNumber'] = $this->utils->parse($d['phoneNumber'],'FR');
        }catch (\Exception $exception) {
            //
        }

        return $d;

    }

    public function detectDelimiter(string $csv_string)
    {

        // List of delimiters that we will check for
        $delimiters = array(';' => 0,',' => 0,"\t" => 0,"|" => 0);

        // For every delimiter, we count the number of time it can be found within the csv string
        foreach ($delimiters as $delimiter => &$count) {
            $count = substr_count($csv_string,$delimiter);
        }

        // The delimiter used is probably the one that has the more occurrence in the file
        return array_search(max($delimiters), $delimiters);

    }

    public function importCsvToArray(string $file, $delimiter =  null,$enclosure = '"')
    {

        // Let's get the content of the file and store it in the string
        $csv_string = file_get_contents($file);

        return $this->parseCSV($csv_string,$delimiter,$enclosure);

    }



}