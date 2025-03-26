<?php

namespace App\Controller\Api;

use App\DTO\ReservationRequest;
use App\Entity\Reservation;
use App\Service\ReservationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/reservations')]
class ReservationController extends AbstractController
{
    public function __construct(
        private ReservationService $reservationService,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $reservations = $this->reservationService->getAllReservations();
        
        return new JsonResponse([
            'status' => 'success',
            'data' => array_map(function(Reservation $reservation) {
                return [
                    'id' => $reservation->getId(),
                    'startDate' => $reservation->getStartDate()->format('Y-m-d'),
                    'endDate' => $reservation->getEndDate()->format('Y-m-d'),
                    'totalPrice' => $reservation->getTotalPrice(),
                    'status' => $reservation->getStatus(),
                    'createdAt' => $reservation->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updatedAt' => $reservation->getUpdatedAt()->format('Y-m-d H:i:s')
                ];
            }, $reservations)
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (!isset($data['startDate']) || !isset($data['endDate'])) {
                throw new \InvalidArgumentException('Start date and end date are required');
            }

            $startDate = new \DateTime($data['startDate']);
            $endDate = new \DateTime($data['endDate']);

            if ($startDate > $endDate) {
                throw new \InvalidArgumentException('Start date cannot be later than end date');
            }

            $reservationRequest = new ReservationRequest($startDate, $endDate);

            $violations = $this->validator->validate($reservationRequest);
            if (count($violations) > 0) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => (string)$violations
                ], Response::HTTP_BAD_REQUEST);
            }

            $reservation = $this->reservationService->createReservation($reservationRequest);

            return new JsonResponse([
                'status' => 'success',
                'data' => [
                    'id' => $reservation->getId(),
                    'startDate' => $reservation->getStartDate()->format('Y-m-d'),
                    'endDate' => $reservation->getEndDate()->format('Y-m-d'),
                    'totalPrice' => $reservation->getTotalPrice(),
                    'status' => $reservation->getStatus(),
                    'createdAt' => $reservation->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updatedAt' => $reservation->getUpdatedAt()->format('Y-m-d H:i:s')
                ]
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'An unexpected error occurred'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/cancel', methods: ['POST'])]
    public function cancel(int $id): JsonResponse
    {
        try {
            $reservation = $this->reservationService->getReservation($id);
            if (!$reservation) {
                throw new \RuntimeException('Reservation not found');
            }

            $this->reservationService->cancelReservation($reservation);

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Reservation cancelled successfully'
            ]);
        } catch (\RuntimeException $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'An unexpected error occurred'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
