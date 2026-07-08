<?php

namespace App\Repository;

use App\Entity\Comentario;
use App\Entity\Publicacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comentario>
 */
class ComentarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comentario::class);
    }

    /**
     * Comentarios de una publicación en orden cronológico.
     *
     * @return Comentario[]
     */
    public function dePublicacion(Publicacion $publicacion): array
    {
        return $this->findBy(['publicacion' => $publicacion], ['fechaCreacion' => 'ASC']);
    }
}
