<?php

namespace App\Security;

use App\Entity\User;
use App\Enum\UserStatut;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->getStatut() !== UserStatut::Actif) {
            throw new CustomUserMessageAccountStatusException('Votre compte est inactif ou suspendu.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
