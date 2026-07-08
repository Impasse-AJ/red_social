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
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;

class RecuperarContrasenaController extends AbstractController
{
    // Mensaje neutro: no revela si el email existe o no (evita enumeración de usuarios)
    private const MENSAJE_NEUTRO = 'Si existe una cuenta con ese correo, recibirás un enlace de recuperación en unos minutos.';

    #[Route('/recuperar-password', name: 'recuperar_password')]
    public function mostrarFormulario(): Response
    {
        return $this->render('recuperar_password.html.twig', [
            'success' => null,
            'error' => null
        ]);
    }

    #[Route('/procesar-recuperacion', name: 'procesar_recuperacion', methods: ['POST'])]
    public function procesarRecuperacion(
        Request $request,
        UsuarioRepository $usuarios,
        EntityManagerInterface $entityManager,
        EmailManager $emailManager,
        RateLimiterFactory $recuperacionPasswordLimiter
    ): Response {
        if (!$this->isCsrfTokenValid('recuperar', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }

        // Límite de solicitudes por IP para evitar abuso
        $limiter = $recuperacionPasswordLimiter->create($request->getClientIp());
        if (!$limiter->consume(1)->isAccepted()) {
            return $this->render('recuperar_password.html.twig', [
                'error' => 'Demasiados intentos. Espera unos minutos antes de volver a intentarlo.',
                'success' => null
            ]);
        }

        $email = trim((string) $request->request->get('email'));
        $usuario = $usuarios->porEmail($email);

        if ($usuario) {
            // Token aleatorio de un solo uso; en BD solo se guarda su hash
            $token = bin2hex(random_bytes(32));
            $usuario->setCodigoRecuperacion(hash('sha256', $token));
            $usuario->setCodigoRecuperacionExpira(new \DateTime('+1 hour'));
            $entityManager->flush();

            $emailManager->enviarRecuperacion($usuario, $token);
        }

        return $this->render('recuperar_password.html.twig', [
            'success' => self::MENSAJE_NEUTRO,
            'error' => null
        ]);
    }

    #[Route('/restablecer-password/{codigo}', name: 'restablecer_password')]
    public function mostrarRestablecerFormulario(string $codigo, UsuarioRepository $usuarios): Response
    {
        $usuario = $this->buscarUsuarioPorCodigo($codigo, $usuarios);

        if (!$usuario) {
            return $this->render('restablecer_password.html.twig', [
                'error' => 'El enlace de recuperación es inválido, ha caducado o ya ha sido utilizado.',
                'codigo' => null
            ]);
        }

        return $this->render('restablecer_password.html.twig', [
            'error' => null,
            'codigo' => $codigo // Se enviará al formulario para procesarlo
        ]);
    }

    #[Route('/procesar-restablecimiento', name: 'procesar_restablecimiento', methods: ['POST'])]
    public function procesarRestablecimiento(
        Request $request,
        UsuarioRepository $usuarios,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if (!$this->isCsrfTokenValid('restablecer', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }

        $codigo = (string) $request->request->get('codigo');
        $nuevaContrasena = (string) $request->request->get('password');
        $confirmacion = (string) $request->request->get('confirm_password');

        $usuario = $this->buscarUsuarioPorCodigo($codigo, $usuarios);

        if (!$usuario) {
            return $this->render('restablecer_password.html.twig', [
                'error' => 'El enlace de recuperación es inválido, ha caducado o ya ha sido utilizado.',
                'codigo' => null
            ]);
        }

        if ($nuevaContrasena !== $confirmacion) {
            return $this->render('restablecer_password.html.twig', [
                'error' => 'Las contraseñas no coinciden.',
                'codigo' => $codigo
            ]);
        }

        if (mb_strlen($nuevaContrasena) < 8) {
            return $this->render('restablecer_password.html.twig', [
                'error' => 'La contraseña debe tener al menos 8 caracteres.',
                'codigo' => $codigo
            ]);
        }

        $usuario->setContrasena($passwordHasher->hashPassword($usuario, $nuevaContrasena));
        $usuario->setCodigoRecuperacion(null); // El enlace es de un solo uso
        $usuario->setCodigoRecuperacionExpira(null);

        $entityManager->flush();

        return $this->redirectToRoute('ctrl_login');
    }

    private function buscarUsuarioPorCodigo(string $codigo, UsuarioRepository $usuarios): ?Usuario
    {
        if ($codigo === '') {
            return null;
        }

        $usuario = $usuarios->findOneBy(['codigoRecuperacion' => hash('sha256', $codigo)]);

        if (!$usuario) {
            return null;
        }

        // Comprobar caducidad del enlace
        $expira = $usuario->getCodigoRecuperacionExpira();
        if (!$expira || $expira < new \DateTime()) {
            return null;
        }

        return $usuario;
    }
}
