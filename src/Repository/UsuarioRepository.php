<?php

namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Usuario>
 */
class UsuarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }

    /**
     * Usuarios activos para la página "Descubrir", con búsqueda opcional por nombre.
     *
     * @return Usuario[]
     */
    public function buscarActivos(Usuario $excluir, string $busqueda = '', int $limite = 50): array
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.id != :yo')
            ->andWhere('u.activo = true')
            ->setParameter('yo', $excluir->getId())
            ->orderBy('u.nombreUsuario', 'ASC')
            ->setMaxResults($limite);

        if ($busqueda !== '') {
            $qb->andWhere('u.nombreUsuario LIKE :busqueda')
               ->setParameter('busqueda', '%' . addcslashes($busqueda, '%_') . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function porEmail(string $email): ?Usuario
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function porNombreUsuario(string $nombreUsuario): ?Usuario
    {
        return $this->findOneBy(['nombreUsuario' => $nombreUsuario]);
    }
}
