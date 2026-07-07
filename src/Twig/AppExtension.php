<?php

namespace App\Twig;

use App\Entity\Amistad;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $em
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

        if (!$usuario) {
            return 0;
        }

        return $this->em->getRepository(Amistad::class)->count([
            'receptor' => $usuario,
            'estado' => 'pendiente',
        ]);
    }
}
