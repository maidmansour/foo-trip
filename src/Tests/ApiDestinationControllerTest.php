<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiDestinationControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/destination');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $destinations = json_decode($responseContent, true);

        $this->assertArrayHasKey('id', $destinations[0]);
        $this->assertArrayHasKey('name', $destinations[0]);
        $this->assertArrayHasKey('description', $destinations[0]);
        $this->assertArrayHasKey('price', $destinations[0]);
        $this->assertArrayHasKey('duration', $destinations[0]);
        $this->assertArrayHasKey('createdAt', $destinations[0]);
        $this->assertArrayHasKey('updatedAt', $destinations[0]);
    }

    public function testCreate(): void
    {
        $client = static::createClient();

        $data = [
            'name' => 'Test Destination',
            'description' => 'Test Description',
            'price' => 100.50,
            'duration' => 7,
            'image' => 'data:image/png;base64,iVBORw...'
        ];

        $client->request('POST', '/api/destination', [], [], [], json_encode($data));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $destination = json_decode($responseContent, true);

        $this->assertArrayHasKey('id', $destination);
        $this->assertEquals($data['name'], $destination['name']);
        $this->assertEquals($data['description'], $destination['description']);
        $this->assertEquals($data['price'], $destination['price']);
        $this->assertEquals($data['duration'], $destination['duration']);
        $this->assertNotNull($destination['createdAt']);
        $this->assertNotNull($destination['updatedAt']);
    }

    public function testUpdate(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $destination = $entityManager->getRepository('App\Entity\Destination')->findOneBy([]);

        if (!$destination) {
            $this->markTestSkipped('No destination found in the database.');
        }

        $data = [
            'id' => $destination->getId(),
            'name' => 'Updated Test Destination',
            'description' => 'Updated Test Description',
            'price' => 150.75,
            'duration' => 10,
            'image' => 'data:image/png;base64,iVBORw...'
        ];

        $client->request('PUT', '/api/destination', [], [], [], json_encode($data));

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $updatedDestination = json_decode($responseContent, true);

        $this->assertEquals($data['name'], $updatedDestination['name']);
        $this->assertEquals($data['description'], $updatedDestination['description']);
        $this->assertEquals($data['price'], $updatedDestination['price']);
        $this->assertEquals($data['duration'], $updatedDestination['duration']);
    }

    public function testDelete(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $destination = $entityManager->getRepository('App\Entity\Destination')->findOneBy([]);

        if (!$destination) {
            $this->markTestSkipped('No destination found in the database.');
        }

        $data = [
            'id' => $destination->getId(),
        ];

        $client->request('DELETE', '/api/destination', [], [], [], json_encode($data));

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $deletedDestination = $entityManager->getRepository('App\Entity\Destination')->find($data['id']);
        $this->assertNull($deletedDestination);
    }
}
