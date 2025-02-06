<?php

namespace App\Controller;

use App\Entity\Comentario;
use App\Entity\Publicacion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ComentarioController extends AbstractController
{
    #[Route('/comentario/agregar/{id}', name: 'agregar_comentario', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // Solo usuarios logueados pueden comentar
    public function agregarComentario(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $publicacion = $entityManager->getRepository(Publicacion::class)->find($id);

        if (!$publicacion) {
            throw $this->createNotFoundException('La publicación no existe.');
        }

        $contenido = $request->request->get('contenido');
        
        if (empty($contenido)) {
            $this->addFlash('error', 'El comentario no puede estar vacío.');
            return $this->redirectToRoute('ver_post', ['id' => $id]);
        }

        $comentario = new Comentario();
        $comentario->setPublicacion($publicacion);
        $comentario->setUsuario($this->getUser()); // Obtener usuario logueado
        $comentario->setContenido($contenido);

        $entityManager->persist($comentario);
        $entityManager->flush();

        $this->addFlash('success', 'Comentario agregado correctamente.');

        return $this->redirectToRoute('ver_post', ['id' => $id]);
    }
}
