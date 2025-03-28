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
use OpenApi\Attributes as OA;

#[Route('/api')]
#[OA\Tag(name: 'Reservations', description: 'Operations on reservations')]
class ReservationController extends AbstractController
{
    public function __construct(
        private ReservationService $reservationService
    ) {
    }

    #[Route('/reservations', name: 'get_reservations', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(
        path: '/api/reservations',
        summary: 'Get reservations',
        description: 'Get list of reservations for logged user',
        tags: ['Reservations'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of reservations',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Reservation')
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Error during reservations retrieval',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ],
        security: [['Bearer' => []]]
    )]
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
    #[OA\Post(
        path: '/api/reservations',
        summary: 'Create new reservation',
        description: 'Creates a new reservation for the logged-in user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['startDate', 'endDate'],
                properties: [
                    new OA\Property(property: 'startDate', type: 'string', format: 'date', example: '2025-04-01'),
                    new OA\Property(property: 'endDate', type: 'string', format: 'date', example: '2025-04-03')
                ]
            )
        ),
        tags: ['Reservations'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Reservation created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Reservation')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: '  Error during reservation creation',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ],
        security: [['Bearer' => []]]
    )]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['startDate']) || !isset($data['endDate'])) {
                throw new \InvalidArgumentException('Start date and end date are required');
            }

            $startDate = new \DateTimeImmutable($data['startDate']);
            $endDate = new \DateTimeImmutable($data['endDate']);

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
    #[OA\Get(
        path: '/api/reservations/{id}',
        summary: 'Get reservation',
        description: 'Get reservation by ID for logged-in user',
        tags: ['Reservations'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Reservation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Reservation')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Error during reservation retrieval',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ],
        security: [['Bearer' => []]]
    )]
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
    #[OA\Post(
        path: '/api/reservations/{id}/cancel',
        summary: 'Cancel reservation',
        description: 'Cancel reservation by ID for logged-in user',
        tags: ['Reservations'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Reservation cancelled',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Reservation')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Error during reservation cancellation',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ],
        security: [['Bearer' => []]]
    )]
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
