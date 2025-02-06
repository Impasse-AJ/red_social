<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Usuario;
use App\Entity\Publicacion;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PerfilController extends AbstractController
{
    #[Route('/perfil/{id}', name: 'ver_perfil')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // 🔐 Solo usuarios autenticados
    public function verPerfil(int $id, EntityManagerInterface $entityManager): Response
    {
        $usuario = $entityManager->getRepository(Usuario::class)->find($id);

        if (!$usuario) {
            throw $this->createNotFoundException("El usuario no existe.");
        }

        $publicaciones = $entityManager->getRepository(Publicacion::class)->findBy(
            ['usuario' => $usuario],
            ['fechaCreacion' => 'DESC'] // 📌 Ordenar publicaciones por fecha
        );

        return $this->render('perfil.html.twig', [
            'usuario' => $usuario,
            'publicaciones' => $publicaciones,
            'propietario' => $this->getUser()->getId() === $usuario->getId(), // 📌 Saber si es su perfil
        ]);
    }

    #[Route('/perfil/{id}/nueva-publicacion', name: 'nueva_publicacion', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // 🔐 Solo usuarios autenticados
    public function nuevaPublicacion(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $usuario = $entityManager->getRepository(Usuario::class)->find($id);

        if (!$usuario || $this->getUser()->getId() !== $usuario->getId()) {
            throw $this->createAccessDeniedException("No puedes publicar en este perfil.");
        }

        $contenido = $request->request->get('contenido');

        if ($contenido) {
            $publicacion = new Publicacion();
            $publicacion->setUsuario($usuario);
            $publicacion->setContenido($contenido);
            $publicacion->setFechaCreacion(new \DateTime());

            $entityManager->persist($publicacion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ver_perfil', ['id' => $id]);
    }
}
