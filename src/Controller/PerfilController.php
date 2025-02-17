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
use Symfony\Component\Filesystem\Filesystem;
use App\Entity\Amistad;

class PerfilController extends AbstractController
{
    #[Route('/perfil/{id}', name: 'ver_perfil')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // 🔐 Solo usuarios autenticados
    public function verPerfil(int $id, EntityManagerInterface $entityManager): Response
    {
        $usuario = $entityManager->getRepository(Usuario::class)->find($id);
        $usuarioActual = $this->getUser();
    
        if (!$usuario) {
            throw $this->createNotFoundException("El usuario no existe.");
        }
    
        // 📌 Comprobar si son amigos
        $amistad = $entityManager->getRepository(Amistad::class)->findOneBy([
            'solicitante' => $usuarioActual,
            'receptor' => $usuario,
            'estado' => 'aceptada'
        ]) ?? $entityManager->getRepository(Amistad::class)->findOneBy([
            'solicitante' => $usuario,
            'receptor' => $usuarioActual,
            'estado' => 'aceptada'
        ]);
    
        $esAmigo = $amistad !== null;
        $propietario = $usuarioActual->getId() === $usuario->getId(); // 📌 Es su propio perfil
    
        // 📌 Comprobar si ya hay una solicitud pendiente
        $solicitudPendiente = $entityManager->getRepository(Amistad::class)->findOneBy([
            'solicitante' => $usuarioActual,
            'receptor' => $usuario,
            'estado' => 'pendiente'
        ]);
    
        // 🔒 Si NO es amigo y NO es su perfil → No mostrar publicaciones
        if (!$esAmigo && !$propietario) {
            return $this->render('perfil.html.twig', [
                'usuario' => $usuario,
                'publicaciones' => [],
                'propietario' => false,
                'privado' => true, // 🚫 Mostrar mensaje de perfil privado
                'solicitudPendiente' => $solicitudPendiente !== null, // 📌 Saber si ya se envió solicitud
            ]);
        }
    
        // 🔓 Si es amigo o es su perfil → Mostrar publicaciones
        $publicaciones = $entityManager->getRepository(Publicacion::class)->findBy(
            ['usuario' => $usuario],
            ['fechaCreacion' => 'DESC'] // 📌 Ordenar publicaciones por fecha
        );
    
        return $this->render('perfil.html.twig', [
            'usuario' => $usuario,
            'publicaciones' => $publicaciones,
            'propietario' => $propietario,
            'privado' => false, // ✅ Puede ver el perfil
            'solicitudPendiente' => $solicitudPendiente !== null, // 📌 Saber si ya se envió solicitud
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
    #[Route('/perfil/{id}/editar', name: 'editar_perfil')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function editarPerfil(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $usuario = $entityManager->getRepository(Usuario::class)->find($id);

        if (!$usuario || $this->getUser()->getId() !== $usuario->getId()) {
            throw $this->createAccessDeniedException("No puedes editar este perfil.");
        }

        if ($request->isMethod('POST')) {
            $nuevoNombre = $request->request->get('nombre_usuario');

            if ($nuevoNombre) {
                $usuario->setNombreUsuario($nuevoNombre);
                $entityManager->flush();

                return $this->redirectToRoute('ver_perfil', ['id' => $id]);
            }
        }

        return $this->render('editar_perfil.html.twig', [
            'usuario' => $usuario,
        ]);
    }
    #[Route('/perfil/{id}/subir-foto', name: 'subir_foto_perfil')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function subirFotoPerfil(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $usuario = $entityManager->getRepository(Usuario::class)->find($id);

        if (!$usuario || $this->getUser()->getId() !== $usuario->getId()) {
            throw $this->createAccessDeniedException("No puedes modificar esta foto de perfil.");
        }

        if ($request->isMethod('POST') && $request->files->get('foto')) {
            $foto = $request->files->get('foto');
            $nombreArchivo = uniqid() . '.' . $foto->guessExtension();

            // Mover la imagen a la carpeta "uploads"
            $foto->move($this->getParameter('foto_perfil_directorio'), $nombreArchivo);

            // Guardar en la BD la nueva ruta de la imagen
            $usuario->setFotoPerfil($nombreArchivo);
            $entityManager->flush();

            return $this->redirectToRoute('ver_perfil', ['id' => $id]);
        }

        return $this->render('subir_foto.html.twig', [
            'usuario' => $usuario,
        ]);
    }
    #[Route('/perfil/{id}/quitar-foto', name: 'quitar_foto_perfil', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function quitarFotoPerfil(int $id, EntityManagerInterface $entityManager): Response
    {
        $usuario = $entityManager->getRepository(Usuario::class)->find($id);

        if (!$usuario || $this->getUser()->getId() !== $usuario->getId()) {
            throw $this->createAccessDeniedException("No puedes modificar esta foto de perfil.");
        }

        if ($usuario->getFotoPerfil()) {
            $filesystem = new Filesystem();
            $rutaFoto = $this->getParameter('foto_perfil_directorio') . '/' . $usuario->getFotoPerfil();
            
            if ($filesystem->exists($rutaFoto)) {
                $filesystem->remove($rutaFoto);
            }

            $usuario->setFotoPerfil(null);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ver_perfil', ['id' => $id]);
    }
}

