<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LoadRPPSCommand extends Command
{
    protected static $defaultName = 'LoadRPPS';

    protected function configure()
    {
        $this
            ->setName('app:rpps:load')
            ->setDescription('Load RPPS Data in the database')
        ;


        $this->addOption(
            'file',
            'file',
            InputOption::VALUE_REQUIRED,
            'the file you want to import'
        );

    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $file = $input->getOption('file');

        if(null === $file) {
            throw new \Exception("file must be set using --file");
        }


        if(!file_exists($file)) {
            throw new \Exception("file $file does not exist");
        }

        $data = $this->importCsvToArray($file);

        $i = 0;

        $data = array_slice($data,50978);

        foreach ($data as $d) {

            try {


                $d = $this->parseData($d);

                $this->em->getConnection()->executeQuery("INSERT INTO rpps_data (number, title,first_name,last_name,speciality,address,postal_code,city,phone_number,finess) VALUES (:number, :title,:firstName,:lastName,:speciality,:address,:postalCode,:city,:phoneNumber,:finess)",$d);

                $output->writeln("Importing {$d['firstName']} {$d['lastName']}");

            }catch (\Exception $exception) {
                $output->writeln($exception->getMessage());
            }
        }

        return 0;

    }

    
    /**
     *
     * This function detects the delimiter inside the CSV file.
     *
     * It allows the function to work with different types of delimiters, ";", "," "\t", or "|"
     *
     *
     *
     * @param string $csv_string    The content of the CSV file
     * @return string               The delimiter used in the CSV file
     */
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

  

}