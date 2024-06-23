<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Plat;
use App\Entity\Utilisateur;
use App\Repository\CommandeRepository;
use App\Repository\PlatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api/commande')]
class CommandeController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CommandeRepository $commandeRepository;
    private PlatRepository $platRepository;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        CommandeRepository $commandeRepository,
        PlatRepository $platRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        TokenStorageInterface $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->commandeRepository = $commandeRepository;
        $this->platRepository = $platRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Retourne la liste de toutes les commandes.
     */
    #[Route('/', name: 'commande_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $commandes = $this->commandeRepository->findAll();
        $data = $this->serializer->serialize($commandes, 'json', ['groups' => 'user:read']);

        return JsonResponse::fromJsonString($data, Response::HTTP_OK);
    }

    /**
     * Crée une nouvelle commande.
     */
    #[Route('/', name: 'commande_new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return $this->json(['message' => 'Token not provided'], Response::HTTP_BAD_REQUEST);
        }

        $user = $token->getUser();

        if (!$user instanceof Utilisateur) {
            return $this->json(['message' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
        }

        $commandeData = json_decode($request->getContent(), true);
        $plats = $commandeData['plats'] ?? [];

        if (empty($plats)) {
            return $this->json(['message' => 'No plats provided'], Response::HTTP_BAD_REQUEST);
        }

        $commande = new Commande();
        $commande->setDateCommande(new \DateTime());
        $commande->setEtat('En cours');
        $commande->setQuantite(5);
        $commande->setNote('');
        $commande->setDateAvis(null);
        $commande->setCommentaire('');
        $commande->setUtilisateur($user);

        $addedPlats = [];
        foreach ($plats as $platId) {
            $plat = $this->platRepository->find($platId);
            if ($plat) {
                $commande->addPlat($plat);
                $addedPlats[] = $plat->getNom();
            } else {
                return $this->json(['message' => 'Invalid plat ID: ' . $platId], Response::HTTP_BAD_REQUEST);
            }
        }

        error_log('Plats added: ' . implode(', ', $addedPlats)); // Debug print

        $errors = $this->validator->validate($commande);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($commande);
        $this->entityManager->flush();

        error_log('Commande saved with ID: ' . $commande->getId()); // Debug print

        $data = $this->serializer->serialize($commande, 'json', ['groups' => 'user:read']);

        return JsonResponse::fromJsonString($data, Response::HTTP_CREATED);
    }

    /**
     * Affiche une commande spécifique.
     */
    #[Route('/{id}', name: 'commande_show', methods: ['GET'])]
    public function show(Commande $commande): JsonResponse
    {
        $data = $this->serializer->serialize($commande, 'json', ['groups' => 'user:read']);

        return JsonResponse::fromJsonString($data, Response::HTTP_OK);
    }

    /**
     * Met à jour une commande spécifique.
     */
    #[Route('/{id}', name: 'commande_edit', methods: ['PUT'])]
    public function edit(Request $request, Commande $commande): JsonResponse
    {
        $this->serializer->deserialize($request->getContent(), Commande::class, 'json', ['object_to_populate' => $commande]);

        $errors = $this->validator->validate($commande);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json($commande, Response::HTTP_OK);
    }

    /**
     * Supprime une commande spécifique.
     */
    #[Route('/{id}', name: 'commande_delete', methods: ['DELETE'])]
    public function delete(Commande $commande): JsonResponse
    {
        $this->entityManager->remove($commande);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
