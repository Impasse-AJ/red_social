<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Publicacion;
use App\Entity\Comentario;
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
        return $this->json(['error' => 'Publicación no encontrada.'], Response::HTTP_NOT_FOUND);
    }

    $contenido = $request->request->get('contenido');

    if (!$contenido) {
        return $this->json(['error' => 'El comentario no puede estar vacío.'], Response::HTTP_BAD_REQUEST);
    }

    // Crear y guardar el comentario
    $comentario = new Comentario();
    $comentario->setPublicacion($publicacion);
    $comentario->setUsuario($this->getUser());
    $comentario->setContenido($contenido);
    $comentario->setFechaCreacion(new \DateTime());

    $entityManager->persist($comentario);
    $entityManager->flush();

    // Devolver respuesta JSON para que JavaScript pueda procesarla
    return $this->json([
        'usuario' => $this->getUser()->getNombreUsuario(),
        'contenido' => $contenido,
        'fecha' => (new \DateTime())->format('d/m/Y H:i')
    ]);
}






}
