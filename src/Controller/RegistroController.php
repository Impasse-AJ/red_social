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
    #[Route('/registro', name: 'registro', methods: ['GET', 'POST'])]
public function register(
    Request $request, 
    EntityManagerInterface $em, 
    MailerInterface $mailer, 
    UserPasswordHasherInterface $passwordHasher
): JsonResponse|Response {
    // Verificar si la solicitud es POST (AJAX)
    if ($request->isMethod('POST')) {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? '';
        $nombreUsuario = $data['nombre_usuario'] ?? '';
        $contrasena = $data['contrasena'] ?? '';

        // Verificar si el usuario o email ya existen
        $emailExistente = $em->getRepository(Usuario::class)->findOneBy(['email' => $email]);
        $usuarioExistente = $em->getRepository(Usuario::class)->findOneBy(['nombreUsuario' => $nombreUsuario]);

        if ($usuarioExistente) {
            return new JsonResponse(['error' => 'El nombre de usuario ya está registrado.'], Response::HTTP_BAD_REQUEST);
        }
        
        if ($emailExistente) {
            return new JsonResponse(['error' => 'El email ya está en uso.'], Response::HTTP_BAD_REQUEST);
        }

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
            // Mostrar el mensaje de error
            return new JsonResponse(['error' => 'Error al guardar en la base de datos: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Generar enlace de activación
        $urlActivacion = $this->generateUrl(
            'activar_cuenta', 
            ['id' => $usuario->getId()], 
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Enviar correo de activación
        try {
            $emailMessage = (new Email())
                ->from('noreply@redsocial.com')
                ->to($email)
                ->subject('Activa tu cuenta')
                ->html("<p>Hola, activa tu cuenta haciendo clic en el siguiente enlace: </p>
                        <a href='$urlActivacion'>Activar Cuenta</a>");

            $mailer->send($emailMessage);

            return new JsonResponse(['success' => 'Se ha enviado un correo de confirmación. Revisa tu correo y activa tu cuenta.']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error al enviar el correo.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Método GET para mostrar el formulario
    return $this->render('registro.html.twig');
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
