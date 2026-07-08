<?php

namespace App\Service;

use App\Entity\Usuario;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Centraliza el envío de correos transaccionales de la aplicación.
 */
class EmailManager
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        #[Autowire('%app.mail_from%')]
        private string $remitente,
    ) {
    }

    public function enviarActivacion(Usuario $usuario): void
    {
        $url = $this->urlGenerator->generate(
            'activar_cuenta',
            ['token' => $usuario->getTokenActivacion()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $this->enviar(
            $usuario->getEmail(),
            'Activa tu cuenta en SymSocial',
            '<p>Hola ' . htmlspecialchars($usuario->getNombreUsuario()) . ', activa tu cuenta haciendo clic en el siguiente enlace:</p>
             <p><a href="' . $url . '">Activar cuenta</a></p>'
        );
    }

    public function enviarRecuperacion(Usuario $usuario, string $token): void
    {
        $url = $this->urlGenerator->generate(
            'restablecer_password',
            ['codigo' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $this->enviar(
            $usuario->getEmail(),
            'Restablecer tu contraseña de SymSocial',
            '<p>Haz clic en el siguiente enlace para restablecer tu contraseña (caduca en 1 hora):</p>
             <p><a href="' . $url . '">Restablecer contraseña</a></p>'
        );
    }

    private function enviar(string $destinatario, string $asunto, string $html): void
    {
        $email = (new Email())
            ->from($this->remitente)
            ->to($destinatario)
            ->subject($asunto)
            ->html($html);

        $this->mailer->send($email);
    }
}
