<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Comentario;

class ComentarioController extends AbstractController
{
    #[Route('/EliminarComentario/{id}', name: 'EliminarComentario', methods: ['DELETE'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function eliminarComentario(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // El token CSRF viaja en la cabecera porque la petición se hace con fetch()
        if (!$this->isCsrfTokenValid('eliminar_comentario', $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['success' => false, 'error' => 'Token CSRF inválido.'], Response::HTTP_FORBIDDEN);
        }

        $comentario = $entityManager->getRepository(Comentario::class)->find($id);

        if (!$comentario) {
            return new JsonResponse(['success' => false, 'error' => 'Comentario no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        if ($comentario->getUsuario() !== $this->getUser()) {
            return new JsonResponse(['success' => false, 'error' => 'No tienes permiso para eliminar este comentario.'], Response::HTTP_FORBIDDEN);
        }

        $entityManager->remove($comentario);
        $entityManager->flush();

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }
}

