<?php

namespace App\Controller;

use App\Entity\Allergene;
use App\Repository\AllergeneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/allergene')]
class AllergeneController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private AllergeneRepository $allergeneRepository;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        AllergeneRepository $allergeneRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->allergeneRepository = $allergeneRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * Retourne la liste de tous les allergènes.
     */
    #[Route('/', name: 'allergene_index', methods: ['GET'])]

    public function index(): JsonResponse
    {
        $allergenes = $this->allergeneRepository->findAll();
        $data = $this->serializer->serialize($allergenes, 'json');

        return JsonResponse::fromJsonString($data, Response::HTTP_OK);
    }

    /**
     * Crée un nouvel allergène.
     */
    #[Route('/', name: 'allergene_new', methods: ['POST'])]

    public function new(Request $request): JsonResponse
    {
        $allergene = $this->serializer->deserialize($request->getContent(), Allergene::class, 'json');

        $errors = $this->validator->validate($allergene);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($allergene);
        $this->entityManager->flush();

        return $this->json($allergene, Response::HTTP_CREATED);
    }

    /**
     * Affiche un allergène spécifique.
     */
    #[Route('/{id}', name: 'allergene_show', methods: ['GET'])]

    public function show(Allergene $allergene): JsonResponse
    {
        $data = $this->serializer->serialize($allergene, 'json');

        return JsonResponse::fromJsonString($data, Response::HTTP_OK);
    }

    /**
     * Met à jour un allergène spécifique.
     */
    #[Route('/{id}', name: 'allergene_edit', methods: ['PUT'])]

    public function edit(Request $request, Allergene $allergene): JsonResponse
    {
        $this->serializer->deserialize($request->getContent(), Allergene::class, 'json', ['object_to_populate' => $allergene]);

        $errors = $this->validator->validate($allergene);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json($allergene, Response::HTTP_OK);
    }

    /**
     * Supprime un allergène spécifique.
     */
    #[Route('/{id}', name: 'allergene_delete', methods: ['DELETE'])]

    public function delete(Allergene $allergene): JsonResponse
    {
        $this->entityManager->remove($allergene);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
