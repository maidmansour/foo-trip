<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des destinations');
    }

    public function testDetail(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $destination = $entityManager->getRepository('App\Entity\Destination')->findOneBy([]);

        if (!$destination) {
            $this->markTestSkipped('No destination found in the database.');
        }

        $client->request('GET', sprintf('/detail/%d', $destination->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $destination->getName());
    }
}
