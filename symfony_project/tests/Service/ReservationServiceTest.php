<?php

namespace App\Tests\Service;

use App\DTO\ReservationRequest;
use App\Entity\DailyAvailability;
use App\Entity\Reservation;
use App\Entity\Settings;
use App\Entity\User;
use App\Repository\DailyAvailabilityRepository;
use App\Repository\ReservationRepository;
use App\Repository\SettingsRepository;
use App\Service\ReservationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class ReservationServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private DailyAvailabilityRepository $dailyAvailabilityRepository;
    private ReservationRepository $reservationRepository;
    private SettingsRepository $settingsRepository;
    private ReservationService $service;
    private User $testUser;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->dailyAvailabilityRepository = $this->createMock(DailyAvailabilityRepository::class);
        $this->reservationRepository = $this->createMock(ReservationRepository::class);
        $this->settingsRepository = $this->createMock(SettingsRepository::class);

        $this->testUser = new User();
        $this->testUser->setUsername('testuser');
        $this->testUser->setPassword('test');
        $this->testUser->setRoles(['ROLE_USER']);

        $this->service = new ReservationService(
            $this->entityManager,
            $this->dailyAvailabilityRepository,
            $this->reservationRepository,
            $this->settingsRepository
        );

        // Setup default settings
        $defaultPrice = new Settings();
        $defaultPrice->setName(Settings::DAILY_PRICE)
            ->setValue('100.00');

        $defaultSpots = new Settings();
        $defaultSpots->setName(Settings::DEFAULT_TOTAL_SPOTS)
            ->setValue('10');

        $this->settingsRepository->method('findByKey')
            ->willReturnMap([
                [Settings::DAILY_PRICE, $defaultPrice],
                [Settings::DEFAULT_TOTAL_SPOTS, $defaultSpots]
            ]);
    }

    public function testCreateReservationSuccess(): void
    {
        // Arrange
        $startDate = new \DateTime('2025-04-01');
        $endDate = new \DateTime('2025-04-03');
        $request = new ReservationRequest($startDate, $endDate);

        $availability = new DailyAvailability();
        $availability->setAvailableSpots(10)->setTotalSpots(10);

        $this->dailyAvailabilityRepository
            ->method('findOneBy')
            ->willReturn($availability);

        $this->entityManager->expects($this->once())->method('beginTransaction');
        $this->entityManager->expects($this->once())->method('commit');
        $this->entityManager->expects($this->never())->method('rollback');

        // Act
        $reservation = $this->service->createReservation($request, $this->testUser);

        // Assert
        $this->assertEquals($startDate->format('Y-m-d'), $reservation->getStartDate());
        $this->assertEquals($endDate->format('Y-m-d'), $reservation->getEndDate());
        $this->assertEquals(200.0, $reservation->getTotalPrice());
        $this->assertEquals('active', $reservation->getStatus());
        $this->assertEquals($this->testUser, $reservation->getUser());
    }

    public function testCreateReservationNoAvailability(): void
    {
        // Arrange
        $startDate = new \DateTime('2025-04-01');
        $endDate = new \DateTime('2025-04-03');
        $request = new ReservationRequest($startDate, $endDate);

        $availability = new DailyAvailability();
        $availability->setAvailableSpots(0)->setTotalSpots(10);

        $this->dailyAvailabilityRepository
            ->method('findOneBy')
            ->willReturn($availability);

        $this->entityManager->expects($this->once())->method('beginTransaction');
        $this->entityManager->expects($this->once())->method('rollback');
        $this->entityManager->expects($this->never())->method('commit');

        // Assert & Act
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No available spots for the selected dates');
        
        $this->service->createReservation($request, $this->testUser);
    }

    public function testCancelReservationSuccess(): void
    {
        // Arrange
        $reservation = new Reservation();
        $reservation->setStatus('active');
        $reservation->setUser($this->testUser);
        $reservation->setStartDate(new \DateTime('2025-04-01'));
        $reservation->setEndDate(new \DateTime('2025-04-03'));

        $this->reservationRepository
            ->method('find')
            ->with(1)
            ->willReturn($reservation);

        // Act
        $this->service->cancelReservation(1, $this->testUser);

        // Assert
        $this->assertEquals('cancelled', $reservation->getStatus());
    }

    public function testCancelReservationNotActive(): void
    {
        // Arrange
        $reservation = new Reservation();
        $reservation->setStatus('cancelled');
        $reservation->setUser($this->testUser);

        $this->reservationRepository
            ->method('find')
            ->with(1)
            ->willReturn($reservation);

        // Assert & Act
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Reservation is not active');
        
        $this->service->cancelReservation(1, $this->testUser);
    }
}
