<?php

namespace App\Service;

use App\DTO\ReservationRequest;
use App\Entity\DailyAvailability;
use App\Entity\Reservation;
use App\Entity\Settings;
use App\Repository\DailyAvailabilityRepository;
use App\Repository\ReservationRepository;
use App\Repository\SettingsRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReservationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DailyAvailabilityRepository $dailyAvailabilityRepository,
        private ReservationRepository $reservationRepository,
        private SettingsRepository $settingsRepository
    ) {
    }

    public function createReservation(ReservationRequest $request): Reservation
    {
        // Check availability for the date range
        if (!$this->isAvailable($request->getStartDate(), $request->getEndDate())) {
            throw new \RuntimeException('No available spots for the selected dates');
        }

        // Calculate total price
        $totalPrice = $this->calculateTotalPrice($request->getStartDate(), $request->getEndDate());

        // Create reservation
        $reservation = new Reservation();
        $reservation->setStartDate($request->getStartDate())
            ->setEndDate($request->getEndDate())
            ->setTotalPrice($totalPrice)
            ->setStatus('active');

        $this->entityManager->persist($reservation);

        // Update availability for each day
        $this->updateAvailability($request->getStartDate(), $request->getEndDate(), -1);

        $this->entityManager->flush();

        return $reservation;
    }

    public function cancelReservation(Reservation $reservation): void
    {
        if ($reservation->getStatus() !== 'active') {
            throw new \RuntimeException('Reservation is not active');
        }

        $reservation->setStatus('cancelled');
        
        // Restore availability for each day
        $this->updateAvailability($reservation->getStartDate(), $reservation->getEndDate(), 1);

        $this->entityManager->flush();
    }

    public function getAllReservations(): array
    {
        return $this->reservationRepository->findAll();
    }

    private function isAvailable(\DateTimeInterface $startDate, \DateTimeInterface $endDate): bool
    {
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $availability = $this->getOrCreateDailyAvailability($currentDate);
            if ($availability->getAvailableSpots() <= 0) {
                return false;
            }
            $currentDate->modify('+1 day');
        }

        return true;
    }

    private function calculateTotalPrice(\DateTimeInterface $startDate, \DateTimeInterface $endDate): float
    {
        $totalPrice = 0.0;
        $currentDate = clone $startDate;

        while ($currentDate <= $endDate) {
            $specialPriceKey = Settings::getSpecialPriceKey($currentDate);
            $specialPrice = $this->settingsRepository->findByKey($specialPriceKey);
            
            if ($specialPrice) {
                $totalPrice += (float)$specialPrice->getValue();
            } else {
                $defaultPrice = $this->settingsRepository->findByKey(Settings::DAILY_PRICE);
                if (!$defaultPrice) {
                    throw new \RuntimeException('Default price not set');
                }
                $totalPrice += (float)$defaultPrice->getValue();
            }

            $currentDate->modify('+1 day');
        }

        return $totalPrice;
    }

    private function updateAvailability(\DateTimeInterface $startDate, \DateTimeInterface $endDate, int $change): void
    {
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $availability = $this->getOrCreateDailyAvailability($currentDate);
            
            if ($change < 0) {
                $availability->decreaseAvailableSpots(abs($change));
            } else {
                $availability->increaseAvailableSpots($change);
            }
            
            $this->entityManager->persist($availability);
            $currentDate->modify('+1 day');
        }
    }

    private function getOrCreateDailyAvailability(\DateTimeInterface $date): DailyAvailability
    {
        $availability = $this->dailyAvailabilityRepository->findOneBy(['date' => $date]);
        
        if (!$availability) {
            $availability = new DailyAvailability();
            $availability->setDate($date);
            
            // Check for special total spots setting
            $specialTotalSpotsKey = Settings::getSpecialTotalSpotsKey($date);
            $specialTotalSpots = $this->settingsRepository->findByKey($specialTotalSpotsKey);
            
            if ($specialTotalSpots) {
                $totalSpots = (int)$specialTotalSpots->getValue();
            } else {
                $defaultTotalSpots = $this->settingsRepository->findByKey(Settings::DEFAULT_TOTAL_SPOTS);
                if (!$defaultTotalSpots) {
                    throw new \RuntimeException('Default total spots not set');
                }
                $totalSpots = (int)$defaultTotalSpots->getValue();
            }
            
            $availability->setTotalSpots($totalSpots);
            $availability->setAvailableSpots($totalSpots);
        }
        
        return $availability;
    }
}
