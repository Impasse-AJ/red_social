<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\Usuario;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RecuperarContraseñaController extends AbstractController
{
    #[Route('/recuperar-password', name: 'recuperar_password')]
    public function mostrarFormulario()
    {
        return $this->render('recuperar_password.html.twig');
    }

    #[Route('/procesar-recuperacion', name: 'procesar_recuperacion', methods: ['POST'])]
    public function procesarRecuperacion(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ) {
        $email = $request->request->get('email');

        // Buscar usuario en la base de datos
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['nombre_usuario' => $email]);

        if (!$usuario) {
            return $this->render('recuperar_password.html.twig', [
                'error' => 'No existe una cuenta asociada a este correo.',
            ]);
        }

        // Generar token único y fecha de expiración
        $token = bin2hex(random_bytes(32));
        $usuario->setTokenRecuperacion($token);
        $usuario->setExpiracionToken(new \DateTime('+30 minutes'));

        $entityManager->persist($usuario);
        $entityManager->flush();

        // Enviar correo con Mailtrap
        $email = (new Email())
            ->from('no-reply@empresa.com')
            ->to($usuario->getNombreUsuario())
            ->subject('Recuperación de Contraseña')
            ->html("<p>Haz clic en el siguiente enlace para restablecer tu contraseña:</p>
                    <a href='" . $this->generateUrl('restablecer_password', ['token' => $token], true) . "'>Restablecer contraseña</a>");

        $mailer->send($email);

        return $this->render('recuperar_password.html.twig', [
            'success' => 'Se ha enviado un correo con instrucciones para restablecer tu contraseña.',
        ]);
    }

    #[Route('/restablecer-password/{token}', name: 'restablecer_password')]
    public function mostrarFormularioRestablecimiento($token, EntityManagerInterface $entityManager)
    {
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['tokenRecuperacion' => $token]);

        if (!$usuario || $usuario->getExpiracionToken() < new \DateTime()) {
            return $this->render('recuperar_password.html.twig', [
                'error' => 'El enlace ha caducado o no es válido.',
            ]);
        }

        return $this->render('restablecer_password.html.twig', [
            'token' => $token,
        ]);
    }

    #[Route('/procesar-restablecimiento', name: 'procesar_restablecimiento', methods: ['POST'])]
    public function procesarRestablecimiento(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $token = $request->request->get('token');
        $nuevaContraseña = $request->request->get('password');

        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['tokenRecuperacion' => $token]);

        if (!$usuario || $usuario->getExpiracionToken() < new \DateTime()) {
            return $this->render('restablecer_password.html.twig', [
                'error' => 'El enlace ha caducado o no es válido.',
            ]);
        }

        // Hash de la nueva contraseña
        $usuario->setContrasena($passwordHasher->hashPassword($usuario, $nuevaContraseña));
        $usuario->setTokenRecuperacion(null);
        $usuario->setExpiracionToken(null);

        $entityManager->persist($usuario);
        $entityManager->flush();

        return $this->redirectToRoute('ctrl_login');
    }
}
