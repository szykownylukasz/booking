<?php

namespace App\Controller\Api;

use App\DTO\ReservationRequest;
use App\Service\ReservationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Context\Normalizer\DateTimeNormalizerContextBuilder;

#[Route('/api')]
class ReservationController extends AbstractController
{
    public function __construct(
        private ReservationService $reservationService
    ) {
    }

    #[Route('/reservations', name: 'get_reservations', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getReservations(): JsonResponse
    {
        try {
            $reservations = $this->reservationService->getAllReservations($this->getUser());
            return $this->json([
                'status' => 'success',
                'data' => $reservations
            ], Response::HTTP_OK, [], ['groups' => ['reservation:read']]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/reservations', name: 'create_reservation', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['startDate']) || !isset($data['endDate'])) {
                throw new \InvalidArgumentException('Start date and end date are required');
            }

            $startDate = new \DateTime($data['startDate']);
            $endDate = new \DateTime($data['endDate']);

            if ($endDate < $startDate) {
                throw new \InvalidArgumentException('End date must be after or equal to start date');
            }

            $reservationRequest = new ReservationRequest($startDate, $endDate);
            $reservation = $this->reservationService->createReservation($reservationRequest, $this->getUser());

            return $this->json([
                'status' => 'success',
                'data' => $reservation
            ], Response::HTTP_CREATED, [], ['groups' => ['reservation:read']]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/reservations/{id}', name: 'get_reservation', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getReservation(int $id): JsonResponse
    {
        try {
            $reservation = $this->reservationService->getReservation($id, $this->getUser());
            
            if (!$reservation) {
                throw new \RuntimeException('Reservation not found');
            }

            return $this->json([
                'status' => 'success',
                'data' => $reservation
            ], Response::HTTP_OK, [], ['groups' => ['reservation:read']]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/reservations/{id}/cancel', name: 'cancel_reservation', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(int $id): JsonResponse
    {
        try {
            $reservation = $this->reservationService->cancelReservation($id, $this->getUser());
            return $this->json([
                'status' => 'success',
                'data' => $reservation
            ], Response::HTTP_OK, [], ['groups' => ['reservation:read']]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
