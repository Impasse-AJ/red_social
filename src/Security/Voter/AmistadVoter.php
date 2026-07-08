<?php

namespace App\Security\Voter;

use App\Entity\Amistad;
use App\Entity\Usuario;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Decide si el usuario autenticado puede responder una solicitud de amistad
 * (solo el receptor de la solicitud).
 *
 * @extends Voter<string, Amistad>
 */
class AmistadVoter extends Voter
{
    public const RESPONDER = 'AMISTAD_RESPONDER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::RESPONDER && $subject instanceof Amistad;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $usuario = $token->getUser();

        if (!$usuario instanceof Usuario) {
            return false;
        }

        /** @var Amistad $subject */
        return $subject->getReceptor()->getId() === $usuario->getId();
    }
}
