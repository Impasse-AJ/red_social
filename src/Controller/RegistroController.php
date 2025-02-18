<?php

namespace App\Controller;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistroController extends AbstractController
{
    #[Route('/registro', name: 'registro')]
    public function register(
        Request $request, 
        EntityManagerInterface $em, 
        MailerInterface $mailer, 
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $success = null;
        $error = null;

        if ($request->isMethod('POST')) {
            // Obtener datos del formulario
            $email = $request->request->get('email');
            $nombreUsuario = $request->request->get('nombre_usuario');
            $contrasena = $request->request->get('contrasena');
            
            // Verificar si el nombre de usuario o email ya existen
            $emailExistente = $em->getRepository(Usuario::class)->findOneBy(['email' => $email]);
            $usuarioExistente = $em->getRepository(Usuario::class)->findOneBy(['nombreUsuario' => $nombreUsuario]);

            if ($usuarioExistente) {
                $error = 'El nombre de usuario ya está registrado.';
            } elseif ($emailExistente) {
                $error = 'El email ya está en uso.';
            } else {
                // Crear nuevo usuario
                $usuario = new Usuario();
                $usuario->setEmail($email);
                $usuario->setNombreUsuario($nombreUsuario);
                $usuario->setContrasena($passwordHasher->hashPassword($usuario, $contrasena)); 
                $usuario->setActivo(false);

                try {
                    $em->persist($usuario);
                    $em->flush();
                } catch (\Exception $e) {
                    return new Response('Error al guardar en la base de datos: ' . $e->getMessage());
                }

                // Generar el enlace de activación
                $urlActivacion = $this->generateUrl(
                    'activar_cuenta', 
                    ['id' => $usuario->getId()], 
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                // Intentar enviar el correo
                try {
                    $emailMessage = (new Email())
                        ->from('noreply@redsocial.com')
                        ->to($email)
                        ->subject('Activa tu cuenta')
                        ->html('<p>Hola, activa tu cuenta haciendo clic en el siguiente enlace: </p>
                               <a href="' . $urlActivacion . '">Activar Cuenta</a>');
                
                    $mailer->send($emailMessage);
                    
                    $success = 'Se ha enviado un correo de confirmación. Revisa tu correo y activa tu cuenta.';
                } catch (\Exception $e) {
                    return new Response('Error al enviar el correo: ' . $e->getMessage());
                }
            }
        }

        return $this->render('registro.html.twig', ['success' => $success, 'error' => $error]);
    }

    #[Route('/activar/{id}', name: 'activar_cuenta')]
    public function activarCuenta(int $id, EntityManagerInterface $em): Response
    {
        $usuario = $em->getRepository(Usuario::class)->find($id);
        
        if (!$usuario) {
            throw $this->createNotFoundException('Cuenta no encontrada.');
        }

        if ($usuario->getActivo()) {
            return $this->redirectToRoute('ctrl_login', ['error' => 'Tu cuenta ya está activada.']);
        }
        
        $usuario->setActivo(true);
        $em->flush();
        
        return $this->redirectToRoute('ctrl_login');
    }
}