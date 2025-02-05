<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use App\Entity\Usuario;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof Usuario) {
            return;
        }

        // Verificar si el usuario está activo
        if (!$user->getActivo()) {
            throw new CustomUserMessageAccountStatusException('Tu cuenta no está activada. Por favor verifica tu correo.');
        }
    }
}
