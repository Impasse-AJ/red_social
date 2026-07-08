<?php

namespace App\Controller;

use App\Entity\Comentario;
use App\Entity\Usuario;
use App\Repository\ComentarioRepository;
use App\Repository\PublicacionRepository;
use App\Security\Voter\PublicacionVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PublicacionController extends AbstractController
{
    #[Route('/publicacion/{id}', name: 'ver_publicacion')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function verPublicacion(
        int $id,
        PublicacionRepository $publicaciones,
        ComentarioRepository $comentarios
    ): Response {
        $publicacion = $publicaciones->find($id);

        if (!$publicacion) {
            throw $this->createNotFoundException("Publicación no encontrada.");
        }

        // La privacidad del perfil aplica también a la publicación suelta
        $this->denyAccessUnlessGranted(PublicacionVoter::VER, $publicacion, 'Debes ser amigo del autor para ver esta publicación.');

        return $this->render('publicacion.html.twig', [
            'publicacion' => $publicacion,
            'comentarios' => $comentarios->dePublicacion($publicacion),
        ]);
    }

    #[Route('/publicacion/{id}/comentar', name: 'comentar_publicacion', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function comentarPublicacion(
        int $id,
        Request $request,
        PublicacionRepository $publicaciones,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('comentar', (string) $request->request->get('_token'))) {
            return $this->json(['error' => 'Token CSRF inválido.'], Response::HTTP_FORBIDDEN);
        }

        $publicacion = $publicaciones->find($id);

        if (!$publicacion) {
            return $this->json(['error' => 'Publicación no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        if (!$this->isGranted(PublicacionVoter::VER, $publicacion)) {
            return $this->json(['error' => 'No puedes comentar esta publicación.'], Response::HTTP_FORBIDDEN);
        }

        $contenido = trim((string) $request->request->get('contenido'));

        if ($contenido === '' || mb_strlen($contenido) > 255) {
            return $this->json(['error' => 'El comentario no puede estar vacío ni superar 255 caracteres.'], Response::HTTP_BAD_REQUEST);
        }

        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        $comentario = new Comentario();
        $comentario->setPublicacion($publicacion);
        $comentario->setUsuario($usuario);
        $comentario->setContenido($contenido);
        $comentario->setFechaCreacion(new \DateTime());

        $entityManager->persist($comentario);
        $entityManager->flush();

        return $this->json([
            'id' => $comentario->getId(),
            'usuario' => $usuario->getNombreUsuario(),
            'contenido' => $contenido,
            'fecha' => $comentario->getFechaCreacion()->format('d/m/Y H:i'),
        ]);
    }
}
