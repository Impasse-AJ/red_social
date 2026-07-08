<?php

namespace App\Security\Voter;

use App\Entity\Usuario;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Decide si el usuario autenticado puede gestionar un perfil
 * (editarlo, publicar en él, cambiar su foto...).
 *
 * @extends Voter<string, Usuario>
 */
class PerfilVoter extends Voter
{
    public const GESTIONAR = 'PERFIL_GESTIONAR';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::GESTIONAR && $subject instanceof Usuario;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $usuario = $token->getUser();

        if (!$usuario instanceof Usuario) {
            return false;
        }

        /** @var Usuario $subject */
        return $usuario->getId() === $subject->getId();
    }
}
