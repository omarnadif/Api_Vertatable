<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/commande')]
class CommandeController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CommandeRepository $commandeRepository;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        CommandeRepository $commandeRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->commandeRepository = $commandeRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * Retourne la liste de toutes les commandes.
     */
    #[Route('/', name: 'commande_index', methods: ['GET'])]

    public function index(): JsonResponse
    {
        $commandes = $this->commandeRepository->findAll();
        $data = $this->serializer->serialize($commandes, 'json');

        return JsonResponse::fromJsonString($data, Response::HTTP_OK);
    }

    /**
     * Crée une nouvelle commande.
     */
    #[Route('/', name: 'commande_new', methods: ['POST'])]

    public function new(Request $request): JsonResponse
    {
        $commande = $this->serializer->deserialize($request->getContent(), Commande::class, 'json');

        $errors = $this->validator->validate($commande);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($commande);
        $this->entityManager->flush();

        return $this->json($commande, Response::HTTP_CREATED);
    }

    /**
     * Affiche une commande spécifique.
     */
    #[Route('/{id}', name: 'commande_show', methods: ['GET'])]

    public function show(Commande $commande): JsonResponse
    {
        $data = $this->serializer->serialize($commande, 'json');

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
