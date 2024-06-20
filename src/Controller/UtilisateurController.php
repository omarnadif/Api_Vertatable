<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api/utilisateur')]
class UtilisateurController extends AbstractController
{
    private EntityManagerInterface $em;
    private UtilisateurRepository $utilisateurRepository;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;
    private UserPasswordHasherInterface $passwordHasher;
    private JWTTokenManagerInterface $jwtManager;
    private UserProviderInterface $userProvider;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        EntityManagerInterface $em,
        UtilisateurRepository $utilisateurRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,
        UserProviderInterface $userProvider,
        TokenStorageInterface $tokenStorage
    ) {
        $this->em = $em;
        $this->utilisateurRepository = $utilisateurRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
        $this->userProvider = $userProvider;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Retourne les détails de l'utilisateur connecté à partir du token.
     */
    #[Route('/me', name: 'utilisateur_me', methods: ['GET'])]
    public function getUserByToken(): JsonResponse
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return $this->json(['message' => 'Token not provided'], Response::HTTP_BAD_REQUEST);
        }

        $user = $token->getUser();

        if (!$user instanceof Utilisateur) {
            return $this->json(['message' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
        }

        $data = $this->serializer->serialize($user, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['entreprise', 'allergenes', 'commandes']
        ]);

        return JsonResponse::fromJsonString($data, Response::HTTP_OK);
    }

    /**
     * Retourne les détails de l'utilisateur.
     */
    #[Route('/{id}', name: 'utilisateur_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->utilisateurRepository->find($id);

        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($user, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['entreprise', 'allergenes', 'commandes']
        ]);

        return JsonResponse::fromJsonString($data, Response::HTTP_OK);
    }

    /**
     * Crée un nouvel utilisateur.
     */
    #[Route('/', name: 'utilisateur_new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $jsonData = json_decode($request->getContent(), true);

        $user = new Utilisateur();
        $user->setEmail($jsonData['email']);
        $user->setRoles($jsonData['roles']);
        $user->setNom($jsonData['nom']);
        $user->setPrenom($jsonData['prenom']);
        $user->setDateDeNaissance(new \DateTime($jsonData['date_de_naissance']));
        $user->setTelephone($jsonData['telephone']);

        $entreprise = $this->em->getRepository('App\Entity\Entreprise')->find($jsonData['entreprise_id']);
        if (!$entreprise) {
            return new JsonResponse(['error' => 'Invalid entreprise ID'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $user->setEntreprise($entreprise);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $jsonData['password']
        );
        $user->setPassword($hashedPassword);

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($user);
        $this->em->flush();

        return $this->json($user, Response::HTTP_CREATED);
    }

    /**
     * Met à jour un utilisateur spécifique.
     */
    #[Route('/{id}', name: 'utilisateur_edit', methods: ['PUT'])]
    public function edit(Request $request, int $id): JsonResponse
    {
        $jsonData = json_decode($request->getContent(), true);

        $user = $this->utilisateurRepository->find($id);
        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $user->setEmail($jsonData['email']);
        $user->setRoles($jsonData['roles']);
        $user->setNom($jsonData['nom']);
        $user->setPrenom($jsonData['prenom']);
        $user->setDateDeNaissance(new \DateTime($jsonData['date_de_naissance']));
        $user->setTelephone($jsonData['telephone']);

        $entreprise = $this->em->getRepository('App\Entity\Entreprise')->find($jsonData['entreprise_id']);
        if (!$entreprise) {
            return new JsonResponse(['error' => 'Invalid entreprise ID'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $user->setEntreprise($entreprise);

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        return $this->json($user, Response::HTTP_OK);
    }
}
