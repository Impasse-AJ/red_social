<?php

namespace App\Repository;

use App\Entity\Amistad;
use App\Entity\Usuario;
use App\Enum\EstadoAmistad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Amistad>
 */
class AmistadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Amistad::class);
    }

    /**
     * Amigos del usuario: usuarios con una amistad aceptada en cualquier sentido.
     *
     * @return Usuario[]
     */
    public function amigosDe(Usuario $usuario): array
    {
        /** @var Amistad[] $amistades */
        $amistades = $this->createQueryBuilder('a')
            ->where('(a.solicitante = :usuario OR a.receptor = :usuario) AND a.estado = :estado')
            ->setParameter('usuario', $usuario)
            ->setParameter('estado', EstadoAmistad::Aceptada)
            ->getQuery()
            ->getResult();

        return array_map(
            fn (Amistad $amistad): Usuario => $amistad->getSolicitante()->getId() === $usuario->getId()
                ? $amistad->getReceptor()
                : $amistad->getSolicitante(),
            $amistades
        );
    }

    /**
     * Amistad aceptada entre dos usuarios, en cualquier sentido.
     */
    public function amistadAceptadaEntre(Usuario $uno, Usuario $otro): ?Amistad
    {
        return $this->createQueryBuilder('a')
            ->where('a.estado = :estado')
            ->andWhere('(a.solicitante = :uno AND a.receptor = :otro) OR (a.solicitante = :otro AND a.receptor = :uno)')
            ->setParameter('estado', EstadoAmistad::Aceptada)
            ->setParameter('uno', $uno)
            ->setParameter('otro', $otro)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function sonAmigos(Usuario $uno, Usuario $otro): bool
    {
        return $this->amistadAceptadaEntre($uno, $otro) !== null;
    }

    /**
     * Estado de la solicitud enviada por un usuario a otro (null si no existe).
     */
    public function estadoDeSolicitud(Usuario $solicitante, Usuario $receptor): ?EstadoAmistad
    {
        $solicitud = $this->findOneBy([
            'solicitante' => $solicitante,
            'receptor' => $receptor,
        ]);

        return $solicitud?->getEstado();
    }

    /**
     * Solicitudes pendientes recibidas por el usuario.
     *
     * @return Amistad[]
     */
    public function solicitudesPendientesDe(Usuario $usuario): array
    {
        return $this->findBy([
            'receptor' => $usuario,
            'estado' => EstadoAmistad::Pendiente,
        ]);
    }

    public function contarPendientesDe(Usuario $usuario): int
    {
        return $this->count([
            'receptor' => $usuario,
            'estado' => EstadoAmistad::Pendiente,
        ]);
    }
}
