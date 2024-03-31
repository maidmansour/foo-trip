<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'ExportDestinations',
    description: 'Export all destinations to a CSV file',
)]
class ExportDestinationsCommand extends Command
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Export all destinations to a CSV file')
            ->setHelp('This command allows you to export all destinations to a CSV file');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', 'http://localhost:8000/api/destination');

        if (200 !== $response->getStatusCode()) {
            $io->error('Failed to fetch destinations from API.');
            return Command::FAILURE;
        }

        $destinations = $response->toArray();

        if (empty($destinations)) {
            $io->warning('No destinations found.');
            return Command::SUCCESS;
        }

        
        $csvFilePath = $this->params->get('kernel.project_dir') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'csv' . DIRECTORY_SEPARATOR . 'destinations' . DIRECTORY_SEPARATOR . 'destinations.csv';
        $csvFile = fopen($csvFilePath, 'w');

        fputcsv($csvFile, ['name', 'description', 'price', 'duration']);

        foreach ($destinations as $destination) {
            fputcsv($csvFile, [
                $destination['name'],
                $destination['description'],
                $destination['price'],
                $destination['duration'] . " day(s)"
            ]);
        }

        fclose($csvFile);

        $io->success('Destinations exported to '. $csvFilePath );

        return Command::SUCCESS;
    }
}
