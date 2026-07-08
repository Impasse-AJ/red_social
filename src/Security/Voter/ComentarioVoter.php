<?php

namespace App\Security\Voter;

use App\Entity\Comentario;
use App\Entity\Usuario;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Decide si el usuario autenticado puede eliminar un comentario (solo el autor).
 *
 * @extends Voter<string, Comentario>
 */
class ComentarioVoter extends Voter
{
    public const ELIMINAR = 'COMENTARIO_ELIMINAR';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::ELIMINAR && $subject instanceof Comentario;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $usuario = $token->getUser();

        if (!$usuario instanceof Usuario) {
            return false;
        }

        /** @var Comentario $subject */
        return $subject->getUsuario()?->getId() === $usuario->getId();
    }
}
