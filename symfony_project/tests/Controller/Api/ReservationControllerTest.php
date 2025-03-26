<?php

namespace App\Tests\Controller\Api;

use App\Entity\Settings;
use App\Repository\SettingsRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ReservationControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        // Prepare default settings
        $this->setupDefaultSettings();
    }

    private function setupDefaultSettings(): void
    {
        $settingsRepository = $this->entityManager->getRepository(Settings::class);

        // Add default price if not exists
        if (!$settingsRepository->findByKey(Settings::DAILY_PRICE)) {
            $defaultPrice = new Settings();
            $defaultPrice->setKey(Settings::DAILY_PRICE)
                ->setValue('100.00')
                ->setUpdatedAt(new \DateTime());
            $this->entityManager->persist($defaultPrice);
        }

        // Add default number of spots if not exists
        if (!$settingsRepository->findByKey(Settings::DEFAULT_TOTAL_SPOTS)) {
            $defaultSpots = new Settings();
            $defaultSpots->setKey(Settings::DEFAULT_TOTAL_SPOTS)
                ->setValue('10')
                ->setUpdatedAt(new \DateTime());
            $this->entityManager->persist($defaultSpots);
        }

        $this->entityManager->flush();
    }

    public function testGetAllReservations(): void
    {
        // Act
        $this->client->request('GET', '/api/reservations');
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);

        // Assert
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('status', $content);
        $this->assertArrayHasKey('data', $content);
        $this->assertEquals('success', $content['status']);
    }

    public function testCreateReservationSuccess(): void
    {
        // Arrange
        $startDate = (new \DateTime())->modify('+1 day')->format('Y-m-d');
        $endDate = (new \DateTime())->modify('+3 days')->format('Y-m-d');
        
        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate
        ];

        // Act
        $this->client->request(
            'POST',
            '/api/reservations',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);

        // Assert
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEquals('success', $content['status']);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('id', $content['data']);
        $this->assertEquals($startDate, $content['data']['startDate']);
        $this->assertEquals($endDate, $content['data']['endDate']);
        $this->assertEquals('active', $content['data']['status']);
    }

    public function testCreateReservationInvalidDates(): void
    {
        // Arrange
        $data = [
            'startDate' => '2025-04-03',
            'endDate' => '2025-04-01' // End date before start date
        ];

        // Act
        $this->client->request(
            'POST',
            '/api/reservations',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);

        // Assert
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals('error', $content['status']);
    }

    public function testCancelReservationSuccess(): void
    {
        // Arrange - Create a reservation first
        $startDate = (new \DateTime())->modify('+1 day')->format('Y-m-d');
        $endDate = (new \DateTime())->modify('+3 days')->format('Y-m-d');
        
        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate
        ];

        $this->client->request(
            'POST',
            '/api/reservations',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $createResponse = json_decode($this->client->getResponse()->getContent(), true);
        $reservationId = $createResponse['data']['id'];

        // Act - Cancel the reservation
        $this->client->request('POST', "/api/reservations/{$reservationId}/cancel");

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);

        // Assert
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('success', $content['status']);
    }

    public function testCancelNonExistentReservation(): void
    {
        // Act
        $this->client->request('POST', '/api/reservations/99999/cancel');

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);

        // Assert
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals('error', $content['status']);
        $this->assertEquals('Reservation not found', $content['message']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up the database
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }
}
