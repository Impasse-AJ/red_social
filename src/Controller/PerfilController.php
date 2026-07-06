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
use Symfony\Component\Validator\Validator\ValidatorInterface;
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
    
        // 📌 Comprobar el estado de la solicitud de amistad
        $solicitud = $entityManager->getRepository(Amistad::class)->findOneBy([
            'solicitante' => $usuarioActual,
            'receptor' => $usuario
        ]);
    
        switch ($solicitud?->getEstado()) { 
            case 'pendiente':
                $solicitudPendiente = 'pendiente';
                break;
            case 'aceptada':
                $solicitudPendiente = 'aceptada';
                break;
            default:
                $solicitudPendiente = 'ninguna';
        }
        
        // 🔒 Si NO es amigo y NO es su perfil → No mostrar publicaciones
        if (!$esAmigo && !$propietario) {
            return $this->render('perfil.html.twig', [
                'usuario' => $usuario,
                'publicaciones' => [],
                'propietario' => false,
                'privado' => true, // 🚫 Mostrar mensaje de perfil privado
                'solicitudPendiente' => $solicitudPendiente, // 📌 Pasamos el estado correcto
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
            'solicitudPendiente' => $solicitudPendiente, // 📌 Pasamos el estado correcto
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

        if (!$this->isCsrfTokenValid('publicar', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }

        $contenido = trim((string) $request->request->get('contenido'));

        if ($contenido !== '' && mb_strlen($contenido) <= 255) {
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
    public function editarPerfil(int $id, Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $usuario = $entityManager->getRepository(Usuario::class)->find($id);

        if (!$usuario || $this->getUser()->getId() !== $usuario->getId()) {
            throw $this->createAccessDeniedException("No puedes editar este perfil.");
        }

        $error = null;

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('editar_perfil', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF inválido.');
            }

            $nuevoNombre = trim((string) $request->request->get('nombre_usuario'));

            // Comprobar que el nombre no esté ya cogido por otro usuario
            $existente = $entityManager->getRepository(Usuario::class)->findOneBy(['nombreUsuario' => $nuevoNombre]);

            if ($existente && $existente->getId() !== $usuario->getId()) {
                $error = 'Ese nombre de usuario ya está en uso.';
            } else {
                $nombreAnterior = $usuario->getNombreUsuario();
                $usuario->setNombreUsuario($nuevoNombre);

                $errores = $validator->validate($usuario);

                if (count($errores) > 0) {
                    $usuario->setNombreUsuario($nombreAnterior);
                    $error = $errores[0]->getMessage();
                } else {
                    $entityManager->flush();

                    return $this->redirectToRoute('ver_perfil', ['id' => $id]);
                }
            }
        }

        return $this->render('editar_perfil.html.twig', [
            'usuario' => $usuario,
            'error' => $error,
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

        $error = null;

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('subir_foto', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF inválido.');
            }

            $foto = $request->files->get('foto');

            if (!$foto || !$foto->isValid()) {
                $error = 'No se ha recibido ninguna imagen válida.';
            } elseif ($foto->getSize() > 2 * 1024 * 1024) {
                $error = 'La imagen no puede superar los 2 MB.';
            } elseif (!in_array($foto->getMimeType(), ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
                // getMimeType() inspecciona el contenido real del archivo, no la extensión
                $error = 'Formato no permitido. Usa JPG, PNG, WEBP o GIF.';
            } else {
                $nombreArchivo = uniqid() . '.' . $foto->guessExtension();
                $foto->move($this->getParameter('foto_perfil_directorio'), $nombreArchivo);

                // Borrar la foto anterior (salvo la imagen por defecto)
                $this->borrarFotoAnterior($usuario);

                $usuario->setFotoPerfil($nombreArchivo);
                $entityManager->flush();

                return $this->redirectToRoute('ver_perfil', ['id' => $id]);
            }
        }

        return $this->render('subir_foto.html.twig', [
            'usuario' => $usuario,
            'error' => $error,
        ]);
    }
    #[Route('/perfil/{id}/quitar-foto', name: 'quitar_foto_perfil', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function quitarFotoPerfil(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('quitar_foto', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }

        $usuario = $entityManager->getRepository(Usuario::class)->find($id);

        if (!$usuario || $this->getUser()->getId() !== $usuario->getId()) {
            throw $this->createAccessDeniedException("No puedes modificar esta foto de perfil.");
        }

        if ($usuario->getFotoPerfil()) {
            $this->borrarFotoAnterior($usuario);
            $usuario->setFotoPerfil(null);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ver_perfil', ['id' => $id]);
    }

    private function borrarFotoAnterior(Usuario $usuario): void
    {
        $fotoActual = $usuario->getFotoPerfil();

        // La imagen por defecto es compartida: no se borra del disco
        if (!$fotoActual || $fotoActual === 'default-profile-picture.jpg') {
            return;
        }

        $filesystem = new Filesystem();
        $rutaFoto = $this->getParameter('foto_perfil_directorio') . '/' . $fotoActual;

        if ($filesystem->exists($rutaFoto)) {
            $filesystem->remove($rutaFoto);
        }
    }
}

