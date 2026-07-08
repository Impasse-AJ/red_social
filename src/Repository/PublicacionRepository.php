<?php

namespace App\Repository;

use App\Entity\Publicacion;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Publicacion>
 */
class PublicacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Publicacion::class);
    }

    /**
     * Feed cronológico: publicaciones de los autores indicados, de más reciente a más antigua.
     *
     * @param Usuario[] $autores
     * @return Publicacion[]
     */
    public function feedDe(array $autores, int $limite = 50): array
    {
        if ($autores === []) {
            return [];
        }

        return $this->createQueryBuilder('p')
            ->where('p.usuario IN (:autores)')
            ->setParameter('autores', $autores)
            ->orderBy('p.fechaCreacion', 'DESC')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }

    /**
     * Publicaciones de un usuario, de más reciente a más antigua.
     *
     * @return Publicacion[]
     */
    public function dePerfil(Usuario $usuario): array
    {
        return $this->findBy(['usuario' => $usuario], ['fechaCreacion' => 'DESC']);
    }
}
