<?php

namespace App\Twig;

use App\Entity\Usuario;
use App\Repository\AmistadRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(
        private Security $security,
        private AmistadRepository $amistades
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('solicitudes_pendientes', [$this, 'solicitudesPendientes']),
        ];
    }

    /**
     * Número de solicitudes de amistad pendientes del usuario autenticado.
     * Se usa en la barra de navegación (badge de notificaciones).
     */
    public function solicitudesPendientes(): int
    {
        $usuario = $this->security->getUser();

        if (!$usuario instanceof Usuario) {
            return 0;
        }

        return $this->amistades->contarPendientesDe($usuario);
    }
}
