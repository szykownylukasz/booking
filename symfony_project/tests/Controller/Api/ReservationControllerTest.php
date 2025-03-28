<?php

namespace App\Tests\Controller\Api;

use App\Entity\Settings;
use App\Entity\User;
use App\Entity\Reservation;
use App\Repository\SettingsRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

class ReservationControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $testUser;
    private $token;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        // Reset database
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        // Create test user
        $this->testUser = new User();
        $this->testUser->setUsername('testuser');
        $this->testUser->setPassword('$2y$13$ESVwrL3ZfCMgwmxrGGxQd.ulhxVcUm3J5PP8ZfB2RPHZwkAQJju4e'); // hashed 'test'
        $this->testUser->setRoles(['ROLE_USER']);
        
        $this->entityManager->persist($this->testUser);

        // Setup default settings
        $defaultPrice = new Settings();
        $defaultPrice->setName(Settings::DAILY_PRICE)
            ->setValue('100.00');
        $this->entityManager->persist($defaultPrice);

        $defaultSpots = new Settings();
        $defaultSpots->setName(Settings::DEFAULT_TOTAL_SPOTS)
            ->setValue('10');
        $this->entityManager->persist($defaultSpots);

        $this->entityManager->flush();

        // Generate JWT token
        $jwtManager = $this->client->getContainer()->get(JWTTokenManagerInterface::class);
        $this->token = $jwtManager->create($this->testUser);
    }

    public function testGetAllReservations(): void
    {
        // Act
        $this->client->request('GET', '/api/reservations', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            'CONTENT_TYPE' => 'application/json'
        ]);
        
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
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
                'CONTENT_TYPE' => 'application/json'
            ],
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
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($data)
        );

        $response = $this->client->getResponse();

        // Assert
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testCancelReservationSuccess(): void
    {
        // Arrange
        $startDate = (new \DateTime())->modify('+1 day');
        $endDate = (new \DateTime())->modify('+3 days');
        
        $reservation = new Reservation();
        $reservation->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setTotalPrice(300.00)
            ->setStatus('active')
            ->setUser($this->testUser);
        
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        // Act
        $this->client->request(
            'POST',
            '/api/reservations/' . $reservation->getId() . '/cancel',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);

        // Assert
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('success', $content['status']);
        $this->assertEquals('cancelled', $content['data']['status']);
    }

    public function testCancelNonExistentReservation(): void
    {
        // Act
        $this->client->request(
            'POST',
            '/api/reservations/99999/cancel',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $response = $this->client->getResponse();

        // Assert
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
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
