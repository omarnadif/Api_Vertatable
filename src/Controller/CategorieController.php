<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/categorie')]
class CategorieController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CategorieRepository $categorieRepository;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        CategorieRepository $categorieRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->categorieRepository = $categorieRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * Retourne la liste de toutes les catégories.
     */
    #[Route('/', name: 'categorie_index', methods: ['GET'])]

    public function index(): JsonResponse
    {
        $categories = $this->categorieRepository->findAll();
        $data = $this->serializer->serialize($categories, 'json');

        return JsonResponse::fromJsonString($data, Response::HTTP_OK);
    }

    /**
     * Crée une nouvelle catégorie.
     */
    #[Route('/', name: 'categorie_new', methods: ['POST'])]

    public function new(Request $request): JsonResponse
    {
        $categorie = $this->serializer->deserialize($request->getContent(), Categorie::class, 'json');

        $errors = $this->validator->validate($categorie);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($categorie);
        $this->entityManager->flush();

        return $this->json($categorie, Response::HTTP_CREATED);
    }

    /**
     * Affiche une catégorie spécifique.
     */
    #[Route('/{id}', name: 'categorie_show', methods: ['GET'])]

    public function show(Categorie $categorie): JsonResponse
    {
        $data = $this->serializer->serialize($categorie, 'json');

        return JsonResponse::fromJsonString($data, Response::HTTP_OK);
    }

    /**
     * Met à jour une catégorie spécifique.
     */
    #[Route('/{id}', name: 'categorie_edit', methods: ['PUT'])]

    public function edit(Request $request, Categorie $categorie): JsonResponse
    {
        $this->serializer->deserialize($request->getContent(), Categorie::class, 'json', ['object_to_populate' => $categorie]);

        $errors = $this->validator->validate($categorie);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json($categorie, Response::HTTP_OK);
    }

    /**
     * Supprime une catégorie spécifique.
     */
    #[Route('/{id}', name: 'categorie_delete', methods: ['DELETE'])]

    public function delete(Categorie $categorie): JsonResponse
    {
        $this->entityManager->remove($categorie);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}

