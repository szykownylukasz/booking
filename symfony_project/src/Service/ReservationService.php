<?php

namespace App\Service;

use App\DTO\ReservationRequest;
use App\Entity\DailyAvailability;
use App\Entity\Reservation;
use App\Entity\Settings;
use App\Entity\User;
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

    public function createReservation(ReservationRequest $request, User $user): Reservation
    {
        $this->entityManager->beginTransaction();
        try {
            // Check availability for the date range
            if (!$this->isAvailable($request->getRawStartDate(), $request->getRawEndDate())) {
                throw new \RuntimeException('No available spots for the selected dates');
            }

            // Calculate total price
            $totalPrice = $this->calculateTotalPrice($request->getRawStartDate(), $request->getRawEndDate());

            // Create reservation
            $reservation = new Reservation();
            $reservation->setStartDate($request->getRawStartDate())
                ->setEndDate($request->getRawEndDate())
                ->setTotalPrice($totalPrice)
                ->setStatus('active')
                ->setUser($user);

            $this->entityManager->persist($reservation);

            // Update availability for each day
            $this->updateAvailability($request->getRawStartDate(), $request->getRawEndDate(), -1);

            $this->entityManager->flush();
            $this->entityManager->commit();

            return $reservation;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    public function cancelReservation(int $reservationId, User $user): Reservation
    {
        $this->entityManager->beginTransaction();
        try {
            $reservation = $this->reservationRepository->find($reservationId);
            
            if (!$reservation) {
                throw new \RuntimeException('Reservation not found');
            }

            if ($reservation->getStatus() !== 'active') {
                throw new \RuntimeException('Reservation is not active');
            }

            if ($reservation->getUser() !== $user && !in_array('ROLE_ADMIN', $user->getRoles())) {
                throw new \RuntimeException('You are not authorized to cancel this reservation');
            }

            $reservation->setStatus('cancelled');
            
            // Restore availability for each day
            $this->updateAvailability($reservation->getRawStartDate(), $reservation->getRawEndDate(), 1);

            $this->entityManager->flush();
            $this->entityManager->commit();

            return $reservation;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    public function getAllReservations(?User $user = null): array
    {
        if ($user === null || in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->reservationRepository->findAll();
        }
        
        return $this->reservationRepository->findBy(['user' => $user]);
    }

    public function getReservation(int $id, ?User $user = null): ?Reservation
    {
        $reservation = $this->reservationRepository->find($id);
        
        if ($reservation === null) {
            return null;
        }

        if ($user === null || in_array('ROLE_ADMIN', $user->getRoles()) || $reservation->getUser() === $user) {
            return $reservation;
        }

        throw new \RuntimeException('You are not authorized to view this reservation');
    }

    public function updateReservation(Reservation $reservation): void
    {
        $this->entityManager->flush();
    }

    private function isAvailable(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): bool
    {
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $availability = $this->getOrCreateDailyAvailability($currentDate);
            if ($availability->getAvailableSpots() <= 0) {
                return false;
            }
            $currentDate = $currentDate->modify('+1 day');
        }

        return true;
    }

    private function calculateTotalPrice(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): float
    {
        $totalPrice = 0.0;
        $currentDate = clone $startDate;

        while ($currentDate <= $endDate) {
            $specialPriceKey = Settings::SPECIAL_DATE_PRICE_PREFIX . $currentDate->format('Y-m-d');
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

            $currentDate = $currentDate->modify('+1 day');
        }

        return $totalPrice;
    }

    private function updateAvailability(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate, int $change): void
    {
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $availability = $this->getOrCreateDailyAvailability($currentDate);
            $availability->setAvailableSpots($availability->getAvailableSpots() + $change);
            $this->entityManager->persist($availability);
            $currentDate = $currentDate->modify('+1 day');
        }
    }

    private function getOrCreateDailyAvailability(\DateTimeImmutable $date): DailyAvailability
    {
        $dateFormatted = $date->format('Y-m-d');
        $date = new \DateTimeImmutable($dateFormatted); // Normalizacja daty do północy

        $availability = $this->dailyAvailabilityRepository->findOneBy(['date' => $date]);
        if (!$availability) {
            // Check for special total spots setting for this date
            $specialTotalSpotsKey = Settings::SPECIAL_TOTAL_SPOTS_PREFIX . $dateFormatted;
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

            $availability = new DailyAvailability();
            $availability->setDate($date)
                ->setTotalSpots($totalSpots)
                ->setAvailableSpots($totalSpots);
            
            $this->entityManager->persist($availability);
            
            // Flush i ponowne pobranie, aby uniknąć duplikatów
            try {
                $this->entityManager->flush();
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                // Jeśli ktoś inny już utworzył rekord, pobierz go
                $availability = $this->dailyAvailabilityRepository->findOneBy(['date' => $date]);
                if (!$availability) {
                    throw $e; // Jeśli nadal nie ma rekordu, coś poszło nie tak
                }
            }
        }

        return $availability;
    }
}
