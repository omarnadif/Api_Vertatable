<?php

namespace App\Controller;

use App\Repository\PlatRepository;
use App\Service\UtilisateurService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Psr\Log\LoggerInterface;

#[Route('/api/plat')]
class PlatController extends AbstractController
{
    private PlatRepository $platRepository;
    private SerializerInterface $serializer;
    private UtilisateurService $utilisateurService;
    private LoggerInterface $logger;

    public function __construct(
        PlatRepository $platRepository,
        SerializerInterface $serializer,
        UtilisateurService $utilisateurService,
        LoggerInterface $logger
    ) {
        $this->platRepository = $platRepository;
        $this->serializer = $serializer;
        $this->utilisateurService = $utilisateurService;
        $this->logger = $logger;
    }

    #[Route('/filtered', name: 'plat_filtered', methods: ['GET'])]
    public function getFilteredPlats(Request $request): JsonResponse
    {
        $userId = $request->query->get('userId');
        $queryParams = $request->query->all();

        if (isset($queryParams['categories']) && is_array($queryParams['categories'])) {
            $categories = $queryParams['categories'];
        } else {
            return $this->json(['error' => 'Categories must be an array'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $allergies = $this->utilisateurService->getUserAllergies($userId);
            $this->logger->info('UserID: ' . $userId);
            $this->logger->info('Allergies: ' . implode(', ', $allergies));
            $this->logger->info('Categories: ' . implode(', ', $categories));
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $this->logger->info('Chiamata findFilteredPlats con parametri:', ['allergies' => $allergies, 'categories' => $categories]);
        $plats = $this->platRepository->findFilteredPlats($allergies, $categories);
        $data = $this->serializer->serialize($plats, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['commandes', 'categorie', 'allergene']]);

        return JsonResponse::fromJsonString($data, Response::HTTP_OK);
    }

    #[Route('/details/{id}', name: 'plat_details', methods: ['GET'])]
    public function getPlatDetails(int $id): JsonResponse
    {
        $plat = $this->platRepository->find($id);
    
        if (!$plat) {
            return $this->json(['error' => 'Plat not found'], Response::HTTP_NOT_FOUND);
        }
    
        $data = $this->serializer->serialize($plat, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['commandes', 'categorie', 'allergene'],
        ]);
    
        return JsonResponse::fromJsonString($data, Response::HTTP_OK);
    }

    #[Route('/available', name: 'plat_available', methods: ['GET'])]
    public function getPlatsByDate(Request $request): JsonResponse
    {
        $dateString = $request->query->get('date');

        if (!$dateString) {
            return $this->json(['error' => 'Date is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $date = new \DateTime($dateString);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid date format'], Response::HTTP_BAD_REQUEST);
        }

        $plats = $this->platRepository->findBy(['date_disponibilite' => $date]);

        $data = $this->serializer->serialize($plats, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['commandes', 'categorie', 'allergene']]);

        return JsonResponse::fromJsonString($data, Response::HTTP_OK);
    }
}

