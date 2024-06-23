<?php

namespace App\Controller;

use App\Entity\Allergene;
use App\Entity\Utilisateur;
use App\Entity\Entreprise;
use App\Repository\UtilisateurRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\AllergeneRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;

#[Route('/api')]
class AuthController extends AbstractController
{
    private UtilisateurRepository $utilisateurRepository;
    private SerializerInterface $serializer;
    private UserPasswordHasherInterface $passwordHasher;
    private LoggerInterface $logger;
    private ManagerRegistry $doctrine;
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(
        UtilisateurRepository $utilisateurRepository,
        SerializerInterface $serializer,
        UserPasswordHasherInterface $passwordHasher,
        LoggerInterface $logger,
        ManagerRegistry $doctrine,
        JWTTokenManagerInterface $jwtManager
    ) {
        $this->utilisateurRepository = $utilisateurRepository;
        $this->serializer = $serializer;
        $this->passwordHasher = $passwordHasher;
        $this->logger = $logger;
        $this->doctrine = $doctrine;
        $this->jwtManager = $jwtManager;
    }

    /**
     * Inscription d'un nouvel utilisateur.
     *
     * @param Request $request La requête HTTP contenant les données de l'utilisateur.
     * @param EntrepriseRepository $entrepriseRepository Le repository des entreprises.
     * @return JsonResponse La réponse en JSON avec les détails de l'utilisateur créé et le token.
     */
    #[Route('/register', name: 'user.register', methods: ['POST'])]
    public function register(Request $request, EntrepriseRepository $entrepriseRepository, AllergeneRepository $allergeneRepository): JsonResponse
    {
        $this->logger->info('Received registration request');

        $jsonData = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Invalid JSON data', ['json_error' => json_last_error_msg()]);
            return new JsonResponse(['error' => 'Invalid JSON data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->logger->info('JSON data parsed successfully', ['jsonData' => $jsonData]);

        $user = new Utilisateur();
        $user->setNom($jsonData['nom']);
        $user->setPrenom($jsonData['prenom']);
        $user->setEmail($jsonData['email']);
        $user->setDateDeNaissance(new \DateTime($jsonData['date_de_naissance']));
        $user->setTelephone($jsonData['telephone']);
        $entreprise = $entrepriseRepository->findOneBy(['codeEntreprise' => $jsonData['codeEntreprise']]);

        if (!$entreprise) {
            return new JsonResponse(['error' => 'Invalid entreprise Code'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $user->setEntreprise($entreprise);

        $this->logger->info('User entity created', ['user' => $user]);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $jsonData['password']
        );
        $user->setPassword($hashedPassword);

        //Allergies 
        if (isset($jsonData['allergenes']) && is_array($jsonData['allergenes'])) {
            foreach ($jsonData['allergenes'] as $allergeneId) {
                $allergene = $allergeneRepository->find($allergeneId);
                if ($allergene) {
                    $user->addAllergene($allergene);
                }
            }
        }

        $this->logger->info('Password hashed successfully', ['hashedPassword' => $hashedPassword]);

        try {
            $this->utilisateurRepository->save($user);
            $this->logger->info('User saved successfully', ['user' => $user]);

            $token = $this->jwtManager->create($user);

            return new JsonResponse([
                'user' => json_decode($this->serializer->serialize($user, 'json', ['groups' => ['user:read', 'user:allergies']]), true),
                'token' => $token
            ], JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->logger->error('Unable to save user', ['exception' => $e->getMessage()]);
            return new JsonResponse(['error' => 'Unable to save user: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Connexion d'un utilisateur.
     *
     * @param AuthenticationUtils $authenticationUtils Utilitaire d'authentification.
     * @return JsonResponse La réponse en JSON avec les détails de l'utilisateur authentifié et le token.
     */
    #[Route('/login', name: 'user.login', methods: ['POST'])]
    public function login(AuthenticationUtils $authenticationUtils): JsonResponse
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            return new JsonResponse(['error' => $error->getMessageKey()], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $user = $this->getUser();
        if (!$user instanceof UserInterface) {
            return new JsonResponse(['error' => 'Invalid credentials'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $token = $this->jwtManager->create($user);

        return new JsonResponse([
            'user' => json_decode($this->serializer->serialize($user, 'json', ['groups' => ['user:read']]), true),
            'token' => $token
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Déconnexion d'un utilisateur.
     *
     * @return JsonResponse La réponse en JSON confirmant la déconnexion.
     */
    #[Route('/logout', name: 'user.logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        return new JsonResponse(['message' => 'Logout successful'], JsonResponse::HTTP_OK);
    }
}

