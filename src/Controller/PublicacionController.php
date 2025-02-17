<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Publicacion;
use App\Entity\Comentario;
use App\Entity\Amistad;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PublicacionController extends AbstractController
{
    #[Route('/publicacion/{id}', name: 'ver_publicacion')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function verPublicacion(int $id, EntityManagerInterface $entityManager): Response
    {
        $publicacion = $entityManager->getRepository(Publicacion::class)->find($id);

        if (!$publicacion) {
            throw $this->createNotFoundException("Publicación no encontrada.");
        }

        // Verificar si el usuario actual es amigo del propietario de la publicación
        $usuario = $this->getUser();
        $usuarioPublicacion = $publicacion->getUsuario(); // Asumiendo que Publicacion tiene un método getUsuario()

        // Comprobar si hay una amistad aceptada entre el usuario actual y el propietario de la publicación
        $amistad = $entityManager->getRepository(Amistad::class)->findOneBy([
            'usuarioA' => $usuario,
            'usuarioB' => $usuarioPublicacion,
            'aceptada' => true,
        ]);

        // Si no es amigo, denegar acceso
        if (!$amistad) {
            throw $this->createAccessDeniedException("No tienes permiso para ver esta publicación.");
        }

        // Obtener los comentarios de la publicación
        $comentarios = $entityManager->getRepository(Comentario::class)->findBy(
            ['publicacion' => $publicacion],
            ['fechaCreacion' => 'ASC']
        );

        return $this->render('publicacion.html.twig', [
            'publicacion' => $publicacion,
            'comentarios' => $comentarios,
        ]);
    }


    #[Route('/publicacion/{id}/comentar', name: 'comentar_publicacion', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function comentarPublicacion(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $publicacion = $entityManager->getRepository(Publicacion::class)->find($id);
    
        if (!$publicacion) {
            throw $this->createNotFoundException("Publicación no encontrada.");
        }
    
        // Verificar si el usuario actual es amigo del propietario de la publicación
        $usuario = $this->getUser();
        $usuarioPublicacion = $publicacion->getUsuario(); // Asumiendo que Publicacion tiene un método getUsuario()
    
        // Comprobar si hay una amistad aceptada entre el usuario actual y el propietario de la publicación
        $amistad = $entityManager->getRepository(Amistad::class)->findOneBy([
            'usuarioA' => $usuario,
            'usuarioB' => $usuarioPublicacion,
            'aceptada' => true,
        ]);
    
        // Si no es amigo, denegar la posibilidad de comentar
        if (!$amistad) {
            throw $this->createAccessDeniedException("No puedes comentar en esta publicación.");
        }
    
        $contenido = $request->request->get('contenido');
    
        if ($contenido) {
            $comentario = new Comentario();
            $comentario->setPublicacion($publicacion);
            $comentario->setUsuario($usuario);
            $comentario->setContenido($contenido);
            $comentario->setFechaCreacion(new \DateTime());
    
            $entityManager->persist($comentario);
            $entityManager->flush();
        }
    
        return $this->redirectToRoute('ver_publicacion', ['id' => $id]);
    }
}
