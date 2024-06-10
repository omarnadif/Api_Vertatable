<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Repository\AdresseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/adresse')]
class AdresseController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private AdresseRepository $adresseRepository;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        AdresseRepository $adresseRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->adresseRepository = $adresseRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }


    #[Route('/', name: 'adresse_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $adresses = $this->adresseRepository->findAll();
        $data = $this->serializer->serialize($adresses, 'json');

        return JsonResponse::fromJsonString($data, Response::HTTP_OK);
    }

    /**
     * Crée une nouvelle adresse.
     */
    #[Route('/', name: 'adresse_new', methods: ['POST'])]

    public function new(Request $request): JsonResponse
    {
        $adresse = $this->serializer->deserialize($request->getContent(), Adresse::class, 'json');

        $errors = $this->validator->validate($adresse);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($adresse);
        $this->entityManager->flush();

        return $this->json($adresse, Response::HTTP_CREATED);
    }

    /**
     * Affiche une adresse spécifique.
     */
    #[Route('/{ID}', name: 'adresse_show', methods: ['GET'])]

    public function show(Adresse $adresse): JsonResponse
    {
        $data = $this->serializer->serialize($adresse, 'json');

        return JsonResponse::fromJsonString($data, Response::HTTP_OK);
    }

    /**
     * Met à jour une adresse spécifique.
     */
    #[Route('/{id}', name: 'adresse_edit', methods: ['PUT'])]
    public function edit(Request $request, Adresse $adresse): JsonResponse
    {
        $this->serializer->deserialize($request->getContent(), Adresse::class, 'json', ['object_to_populate' => $adresse]);

        $errors = $this->validator->validate($adresse);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json($adresse, Response::HTTP_OK);
    }

    /**
     * Supprime une adresse spécifique.
     */
    #[Route('/{id}', name: 'adresse_delete', methods: ['DELETE'])]

    public function delete(Adresse $adresse): JsonResponse
    {
        $this->entityManager->remove($adresse);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
