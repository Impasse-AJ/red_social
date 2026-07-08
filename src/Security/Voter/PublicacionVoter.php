<?php

namespace App\Security\Voter;

use App\Entity\Publicacion;
use App\Entity\Usuario;
use App\Repository\AmistadRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Decide si el usuario autenticado puede ver una publicación:
 * solo el autor o sus amigos (la privacidad del perfil se aplica también aquí).
 *
 * @extends Voter<string, Publicacion>
 */
class PublicacionVoter extends Voter
{
    public const VER = 'PUBLICACION_VER';

    public function __construct(private AmistadRepository $amistadRepository)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VER && $subject instanceof Publicacion;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $usuario = $token->getUser();

        if (!$usuario instanceof Usuario) {
            return false;
        }

        /** @var Publicacion $subject */
        $autor = $subject->getUsuario();

        if ($autor === null) {
            return false;
        }

        if ($autor->getId() === $usuario->getId()) {
            return true;
        }

        return $this->amistadRepository->sonAmigos($usuario, $autor);
    }
}
