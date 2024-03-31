<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AdminControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des destinations');
    }

    public function testCreate(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/admin/create');

        $form = $crawler->selectButton('save')->form([
            'destination[name]' => 'Nouvelle Destination',
            'destination[description]' => 'Description de la nouvelle destination',
            'destination[price]' => 100.50,
            'destination[duration]' => 7,
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/admin');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'La destination a bien été crée');
    }

    public function testEdit(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        $destination = $entityManager->getRepository('App\Entity\Destination')->findOneBy([]);

        if (!$destination) {
            $this->markTestSkipped('No destination found in the database.');
        }

        $crawler = $client->request('GET', sprintf('/admin/edit/%d', $destination->getId()));

        $form = $crawler->selectButton('save')->form([
            'destination[name]' => 'Destination modifiée',
            'destination[description]' => 'Description modifiée',
            'destination[price]' => 150.75,
            'destination[duration]' => 10,
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/admin');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'La destination a bien été modifiée');
    }

    public function testDelete(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        $destination = $entityManager->getRepository('App\Entity\Destination')->findOneBy([]);

        if (!$destination) {
            $this->markTestSkipped('No destination found in the database.');
        }

        $client->request('DELETE', sprintf('/admin/delete/%d', $destination->getId()));

        $this->assertResponseRedirects('/admin');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'La destination a bien été supprimée');
    }
}
