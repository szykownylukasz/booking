<?php

namespace App\Controller;

use App\Entity\Settings;
use App\Repository\SettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

#[Route('/api')]
class SettingsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SettingsRepository $settingsRepository
    ) {}

    #[Route('/settings', name: 'get_settings', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Response(
        response: 200,
        description: 'Returns global settings',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'maxReservationsPerDay', type: 'integer'),
                new OA\Property(property: 'pricePerDay', type: 'number', format: 'float')
            ]
        )
    )]
    #[OA\Tag(name: 'Settings')]
    public function getSettings(): JsonResponse
    {
        $defaultTotalSpots = $this->settingsRepository->findOneBy(['key' => Settings::DEFAULT_TOTAL_SPOTS]);
        $dailyPrice = $this->settingsRepository->findOneBy(['key' => Settings::DAILY_PRICE]);

        return new JsonResponse([
            'maxReservationsPerDay' => $defaultTotalSpots ? (int)$defaultTotalSpots->getValue() : 1,
            'pricePerDay' => $dailyPrice ? (float)$dailyPrice->getValue() : 100.0
        ]);
    }

    #[Route('/settings', name: 'update_settings', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            required: ['maxReservationsPerDay', 'pricePerDay'],
            properties: [
                new OA\Property(property: 'maxReservationsPerDay', type: 'integer', minimum: 1),
                new OA\Property(property: 'pricePerDay', type: 'number', format: 'float', minimum: 0)
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Settings updated successfully',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    #[OA\Tag(name: 'Settings')]
    public function updateSettings(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['maxReservationsPerDay']) || !isset($data['pricePerDay'])) {
            return new JsonResponse(['error' => 'Missing required fields'], 400);
        }

        $maxReservations = (int)$data['maxReservationsPerDay'];
        $pricePerDay = (float)$data['pricePerDay'];

        if ($maxReservations < 1) {
            return new JsonResponse(['error' => 'maxReservationsPerDay must be at least 1'], 400);
        }

        if ($pricePerDay < 0) {
            return new JsonResponse(['error' => 'pricePerDay cannot be negative'], 400);
        }

        $defaultTotalSpots = $this->settingsRepository->findOneBy(['key' => Settings::DEFAULT_TOTAL_SPOTS]);
        if (!$defaultTotalSpots) {
            $defaultTotalSpots = new Settings();
            $defaultTotalSpots->setKey(Settings::DEFAULT_TOTAL_SPOTS);
        }
        $defaultTotalSpots->setValue((string)$maxReservations);

        $dailyPrice = $this->settingsRepository->findOneBy(['key' => Settings::DAILY_PRICE]);
        if (!$dailyPrice) {
            $dailyPrice = new Settings();
            $dailyPrice->setKey(Settings::DAILY_PRICE);
        }
        $dailyPrice->setValue((string)$pricePerDay);

        $this->entityManager->persist($defaultTotalSpots);
        $this->entityManager->persist($dailyPrice);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Settings updated successfully']);
    }
}
