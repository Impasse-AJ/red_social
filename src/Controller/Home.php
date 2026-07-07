<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Usuario;
use App\Entity\Amistad;
use App\Entity\Publicacion;

class Home extends AbstractController
{
    #[Route('/home', name: 'ctrl_home')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function home(EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser();

        // Amistades aceptadas del usuario (en cualquiera de los dos sentidos)
        $amistades = $entityManager->getRepository(Amistad::class)->createQueryBuilder('a')
            ->where('(a.solicitante = :usuario OR a.receptor = :usuario) AND a.estado = :estado')
            ->setParameter('usuario', $usuario)
            ->setParameter('estado', 'aceptada')
            ->getQuery()
            ->getResult();

        // El feed incluye las publicaciones propias y las de los amigos
        $autores = [$usuario];
        foreach ($amistades as $amistad) {
            $autores[] = $amistad->getSolicitante()->getId() === $usuario->getId()
                ? $amistad->getReceptor()
                : $amistad->getSolicitante();
        }

        $publicaciones = $entityManager->getRepository(Publicacion::class)->createQueryBuilder('p')
            ->where('p.usuario IN (:autores)')
            ->setParameter('autores', $autores)
            ->orderBy('p.fechaCreacion', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();

        return $this->render('home.html.twig', [
            'usuario' => $usuario,
            'publicaciones' => $publicaciones,
            'amigos' => count($autores) - 1,
        ]);
    }

    #[Route('/descubrir', name: 'descubrir')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function descubrir(Request $request, EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser();
        $busqueda = trim((string) $request->query->get('q'));

        $qb = $entityManager->getRepository(Usuario::class)->createQueryBuilder('u')
            ->where('u.id != :yo')
            ->andWhere('u.activo = true')
            ->setParameter('yo', $usuario->getId())
            ->orderBy('u.nombreUsuario', 'ASC')
            ->setMaxResults(50);

        if ($busqueda !== '') {
            $qb->andWhere('u.nombreUsuario LIKE :busqueda')
               ->setParameter('busqueda', '%' . $busqueda . '%');
        }

        return $this->render('descubrir.html.twig', [
            'usuarios' => $qb->getQuery()->getResult(),
            'busqueda' => $busqueda,
        ]);
    }
}
