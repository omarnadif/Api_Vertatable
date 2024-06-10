<?php
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    // auto hasher with default options for the Utilisateur class (and children)
    $security->passwordHasher(Utilisateur::class)
        ->algorithm('auto');

    // auto hasher with custom options for all PasswordAuthenticatedUserInterface instances
    $security->passwordHasher(PasswordAuthenticatedUserInterface::class)
        ->algorithm('auto')
        ->cost(15);
};
