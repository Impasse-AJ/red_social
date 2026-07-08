<?php

namespace App\Controller;

use App\Entity\Publicacion;
use App\Entity\Usuario;
use App\Enum\EstadoAmistad;
use App\Repository\AmistadRepository;
use App\Repository\PublicacionRepository;
use App\Repository\UsuarioRepository;
use App\Security\Voter\PerfilVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PerfilController extends AbstractController
{
    private const FOTO_POR_DEFECTO = 'default-profile-picture.jpg';

    #[Route('/perfil/{id}', name: 'ver_perfil')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function verPerfil(
        int $id,
        UsuarioRepository $usuarios,
        AmistadRepository $amistades,
        PublicacionRepository $publicaciones
    ): Response {
        $usuario = $usuarios->find($id);

        /** @var Usuario $usuarioActual */
        $usuarioActual = $this->getUser();

        if (!$usuario) {
            throw $this->createNotFoundException("El usuario no existe.");
        }

        $propietario = $usuarioActual->getId() === $usuario->getId();
        $esAmigo = !$propietario && $amistades->sonAmigos($usuarioActual, $usuario);

        // Estado de la relación para la vista: aceptada > pendiente > ninguna
        if ($esAmigo) {
            $solicitudPendiente = 'aceptada';
        } elseif ($amistades->estadoDeSolicitud($usuarioActual, $usuario) === EstadoAmistad::Pendiente) {
            $solicitudPendiente = 'pendiente';
        } else {
            $solicitudPendiente = 'ninguna';
        }

        // Las publicaciones solo son visibles para el propietario y sus amigos
        $puedeVer = $propietario || $esAmigo;

        return $this->render('perfil.html.twig', [
            'usuario' => $usuario,
            'publicaciones' => $puedeVer ? $publicaciones->dePerfil($usuario) : [],
            'propietario' => $propietario,
            'privado' => !$puedeVer,
            'solicitudPendiente' => $solicitudPendiente,
        ]);
    }

    #[Route('/perfil/{id}/nueva-publicacion', name: 'nueva_publicacion', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function nuevaPublicacion(
        Request $request,
        int $id,
        UsuarioRepository $usuarios,
        EntityManagerInterface $entityManager
    ): Response {
        $usuario = $usuarios->find($id);

        if (!$usuario) {
            throw $this->createNotFoundException("El usuario no existe.");
        }

        $this->denyAccessUnlessGranted(PerfilVoter::GESTIONAR, $usuario, 'No puedes publicar en este perfil.');

        if (!$this->isCsrfTokenValid('publicar', (string) $request->request->get('_token'))) {
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

        // Desde el feed se vuelve al feed; desde el perfil, al perfil
        if ($request->request->get('redirect') === 'home') {
            return $this->redirectToRoute('ctrl_home');
        }

        return $this->redirectToRoute('ver_perfil', ['id' => $id]);
    }

    #[Route('/perfil/{id}/editar', name: 'editar_perfil')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function editarPerfil(
        int $id,
        Request $request,
        UsuarioRepository $usuarios,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response {
        $usuario = $usuarios->find($id);

        if (!$usuario) {
            throw $this->createNotFoundException("El usuario no existe.");
        }

        $this->denyAccessUnlessGranted(PerfilVoter::GESTIONAR, $usuario, 'No puedes editar este perfil.');

        $error = null;

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('editar_perfil', (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF inválido.');
            }

            $nuevoNombre = trim((string) $request->request->get('nombre_usuario'));

            // Comprobar que el nombre no esté ya cogido por otro usuario
            $existente = $usuarios->porNombreUsuario($nuevoNombre);

            if ($existente && $existente->getId() !== $usuario->getId()) {
                $error = 'Ese nombre de usuario ya está en uso.';
            } else {
                $nombreAnterior = $usuario->getNombreUsuario();
                $usuario->setNombreUsuario($nuevoNombre);

                $errores = $validator->validate($usuario);

                if (count($errores) > 0) {
                    $usuario->setNombreUsuario($nombreAnterior);
                    $error = (string) $errores[0]->getMessage();
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
    public function subirFotoPerfil(
        Request $request,
        int $id,
        UsuarioRepository $usuarios,
        EntityManagerInterface $entityManager
    ): Response {
        $usuario = $usuarios->find($id);

        if (!$usuario) {
            throw $this->createNotFoundException("El usuario no existe.");
        }

        $this->denyAccessUnlessGranted(PerfilVoter::GESTIONAR, $usuario, 'No puedes modificar esta foto de perfil.');

        $error = null;

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('subir_foto', (string) $request->request->get('_token'))) {
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
    public function quitarFotoPerfil(
        int $id,
        Request $request,
        UsuarioRepository $usuarios,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('quitar_foto', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }

        $usuario = $usuarios->find($id);

        if (!$usuario) {
            throw $this->createNotFoundException("El usuario no existe.");
        }

        $this->denyAccessUnlessGranted(PerfilVoter::GESTIONAR, $usuario, 'No puedes modificar esta foto de perfil.');

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
        if (!$fotoActual || $fotoActual === self::FOTO_POR_DEFECTO) {
            return;
        }

        $filesystem = new Filesystem();
        $rutaFoto = $this->getParameter('foto_perfil_directorio') . '/' . $fotoActual;

        if ($filesystem->exists($rutaFoto)) {
            $filesystem->remove($rutaFoto);
        }
    }
}
