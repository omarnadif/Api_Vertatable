<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/entreprise')]
class EntrepriseController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private EntrepriseRepository $entrepriseRepository;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        EntrepriseRepository $entrepriseRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->entrepriseRepository = $entrepriseRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('/', name: 'entreprise_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $entreprises = $this->entrepriseRepository->findAll();
        $data = $this->serializer->serialize($entreprises, 'json');

        return JsonResponse::fromJsonString($data, Response::HTTP_OK);
    }

    #[Route('/', name: 'entreprise_new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $entreprise = $this->serializer->deserialize($request->getContent(), Entreprise::class, 'json');

        $errors = $this->validator->validate($entreprise);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($entreprise);
        $this->entityManager->flush();

        return $this->json($entreprise, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'entreprise_show', methods: ['GET'])]
    public function show(Entreprise $entreprise): JsonResponse
    {
        $data = $this->serializer->serialize($entreprise, 'json');

        return JsonResponse::fromJsonString($data, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'entreprise_edit', methods: ['PUT'])]
    public function edit(Request $request, Entreprise $entreprise): JsonResponse
    {
        $this->serializer->deserialize($request->getContent(), Entreprise::class, 'json', ['object_to_populate' => $entreprise]);

        $errors = $this->validator->validate($entreprise);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json($entreprise, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'entreprise_delete', methods: ['DELETE'])]
    public function delete(Entreprise $entreprise): JsonResponse
    {
        $this->entityManager->remove($entreprise);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
