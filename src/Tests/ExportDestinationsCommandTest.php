<?php

namespace App\Tests;

use App\Command\ExportDestinationsCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class ExportDestinationsCommandTest extends KernelTestCase
{
    private $commandTester;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        // Replace the real HTTP client with a mock
        $httpClient = new MockHttpClient(new MockResponse(json_encode([
            [
                'name' => 'Destination 1',
                'description' => 'Description 1',
                'price' => 100,
                'duration' => 5
            ],
            [
                'name' => 'Destination 2',
                'description' => 'Description 2',
                'price' => 150,
                'duration' => 7
            ]
        ])));

        $application->add(new ExportDestinationsCommand($kernel->getContainer()->getParameterBag()));
        $command = $application->find('ExportDestinations');
        $this->commandTester = new CommandTester($command);
    }

    public function testExecute(): void
    {
        $this->commandTester->execute([]);

        // Verify the command output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Destinations exported to destinations.csv.', $output);

        // Verify the CSV file content
        $csvFilePath = self::$kernel->getContainer()->getParameter('kernel.project_dir') . '/public/csv/destinations/destinations.csv';
        $this->assertFileExists($csvFilePath);

        $csvContent = file_get_contents($csvFilePath);
        $expectedCsvContent = "name,description,price,duration\n";
        $expectedCsvContent .= "Destination 1,Description 1,100,5 day(s)\n";
        $expectedCsvContent .= "Destination 2,Description 2,150,7 day(s)\n";

        $this->assertEquals($expectedCsvContent, $csvContent);
    }
}
