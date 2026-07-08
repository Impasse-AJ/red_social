<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use App\Service\EmailManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistroController extends AbstractController
{
    #[Route('/registro', name: 'registro')]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UsuarioRepository $usuarios,
        EmailManager $emailManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): Response {
        $success = null;
        $error = null;

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('registro', (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF inválido.');
            }

            $email = trim((string) $request->request->get('email'));
            $nombreUsuario = trim((string) $request->request->get('nombre_usuario'));
            $contrasena = (string) $request->request->get('contrasena');

            if ($usuarios->porNombreUsuario($nombreUsuario) !== null) {
                $error = 'El nombre de usuario ya está registrado.';
            } elseif ($usuarios->porEmail($email) !== null) {
                $error = 'El email ya está en uso.';
            } elseif (mb_strlen($contrasena) < 8) {
                $error = 'La contraseña debe tener al menos 8 caracteres.';
            } elseif (mb_strlen($contrasena) > 4096) {
                $error = 'La contraseña es demasiado larga.';
            } else {
                // Crear nuevo usuario con token de activación aleatorio
                $usuario = new Usuario();
                $usuario->setEmail($email);
                $usuario->setNombreUsuario($nombreUsuario);
                $usuario->setContrasena($passwordHasher->hashPassword($usuario, $contrasena));
                $usuario->setActivo(false);
                $usuario->setTokenActivacion(bin2hex(random_bytes(32)));

                // Validar los datos del usuario (email, nombre de usuario)
                $errores = $validator->validate($usuario);

                if (count($errores) > 0) {
                    $error = (string) $errores[0]->getMessage();
                } else {
                    $em->persist($usuario);
                    $em->flush();

                    try {
                        $emailManager->enviarActivacion($usuario);
                        $success = 'Se ha enviado un correo de confirmación. Revisa tu correo y activa tu cuenta.';
                    } catch (\Exception) {
                        $error = 'No se pudo enviar el correo de activación. Inténtalo de nuevo más tarde.';
                    }
                }
            }
        }

        return $this->render('registro.html.twig', ['success' => $success, 'error' => $error]);
    }

    #[Route('/activar/{token}', name: 'activar_cuenta')]
    public function activarCuenta(string $token, UsuarioRepository $usuarios, EntityManagerInterface $em): Response
    {
        $usuario = $usuarios->findOneBy(['tokenActivacion' => $token]);

        if (!$usuario) {
            throw $this->createNotFoundException('El enlace de activación no es válido o ya ha sido utilizado.');
        }

        $usuario->setActivo(true);
        $usuario->setTokenActivacion(null); // El token es de un solo uso
        $em->flush();

        return $this->redirectToRoute('ctrl_login');
    }
}
