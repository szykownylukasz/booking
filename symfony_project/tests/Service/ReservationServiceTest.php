<?php

namespace App\Tests\Service;

use App\DTO\ReservationRequest;
use App\Entity\DailyAvailability;
use App\Entity\Reservation;
use App\Entity\Settings;
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

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->dailyAvailabilityRepository = $this->createMock(DailyAvailabilityRepository::class);
        $this->reservationRepository = $this->createMock(ReservationRepository::class);
        $this->settingsRepository = $this->createMock(SettingsRepository::class);

        $this->service = new ReservationService(
            $this->entityManager,
            $this->dailyAvailabilityRepository,
            $this->reservationRepository,
            $this->settingsRepository
        );
    }

    public function testCreateReservationSuccess(): void
    {
        // Arrange
        $startDate = new \DateTime('2025-04-01');
        $endDate = new \DateTime('2025-04-03');
        $request = new ReservationRequest($startDate, $endDate);

        $defaultPrice = new Settings();
        $defaultPrice->setValue('100.00');
        
        $defaultSpots = new Settings();
        $defaultSpots->setValue('10');

        $this->settingsRepository
            ->method('findByKey')
            ->willReturnMap([
                [Settings::DAILY_PRICE, $defaultPrice],
                [Settings::DEFAULT_TOTAL_SPOTS, $defaultSpots],
            ]);

        $availability = new DailyAvailability();
        $availability->setAvailableSpots(10)->setTotalSpots(10);

        $this->dailyAvailabilityRepository
            ->method('findOneBy')
            ->willReturn($availability);

        $this->entityManager->expects($this->once())->method('beginTransaction');
        $this->entityManager->expects($this->once())->method('commit');
        $this->entityManager->expects($this->never())->method('rollback');

        // Act
        $reservation = $this->service->createReservation($request);

        // Assert
        $this->assertEquals($startDate, $reservation->getStartDate());
        $this->assertEquals($endDate, $reservation->getEndDate());
        $this->assertEquals(300.0, $reservation->getTotalPrice()); // 3 dni * 100
        $this->assertEquals('active', $reservation->getStatus());
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
        
        $this->service->createReservation($request);
    }

    public function testCancelReservationSuccess(): void
    {
        // Arrange
        $startDate = new \DateTime('2025-04-01');
        $endDate = new \DateTime('2025-04-03');
        
        $reservation = new Reservation();
        $reservation->setStatus('active')
                   ->setStartDate($startDate)
                   ->setEndDate($endDate);

        $availability = new DailyAvailability();
        $availability->setAvailableSpots(9)->setTotalSpots(10);

        $this->dailyAvailabilityRepository
            ->method('findOneBy')
            ->willReturn($availability);

        $this->entityManager->expects($this->once())->method('beginTransaction');
        $this->entityManager->expects($this->once())->method('commit');
        $this->entityManager->expects($this->never())->method('rollback');

        // Act
        $this->service->cancelReservation($reservation);

        // Assert
        $this->assertEquals('cancelled', $reservation->getStatus());
    }

    public function testCancelReservationNotActive(): void
    {
        // Arrange
        $reservation = new Reservation();
        $reservation->setStatus('cancelled');

        $this->entityManager->expects($this->once())->method('beginTransaction');
        $this->entityManager->expects($this->once())->method('rollback');
        $this->entityManager->expects($this->never())->method('commit');

        // Assert & Act
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Reservation is not active');
        
        $this->service->cancelReservation($reservation);
    }
}
