<?php

namespace App\Service;

use App\Repository\UtilisateurRepository;

class UtilisateurService
{
    private UtilisateurRepository $utilisateurRepository;

    public function __construct(UtilisateurRepository $utilisateurRepository)
    {
        $this->utilisateurRepository = $utilisateurRepository;
    }

    /**
     * Retourne les allergènes de l'utilisateur.
     */
    public function getUserAllergies(int $userId): array
    {
        $user = $this->utilisateurRepository->find($userId);
        if (!$user) {
            throw new \Exception('User not found');
        }

        $allergies = $user->getAllergenes()->toArray();
        $allergiesArray = [];
        foreach ($allergies as $allergie) {
            $allergiesArray[] = $allergie->getNom();
        }

        return $allergiesArray;
    }
}
