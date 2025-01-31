<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\Usuario;

class RecuperarContraseñaController extends AbstractController
{
    #[Route('/recuperar-password', name: 'recuperar_password')]
    public function mostrarFormulario()
    {
        return $this->render('recuperar_password.html.twig',[
            'success' =>null,'error'=>null
        ]);
    }

    #[Route('/procesar-recuperacion', name: 'procesar_recuperacion', methods: ['POST'])]
    public function procesarRecuperacion(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ) {
        $email = $request->request->get('email');

        // Buscar usuario en la base de datos
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['nombreUsuario' => $email]);

        if (!$usuario) {
            return $this->render('recuperar_password.html.twig', [
                'error' => 'No existe una cuenta asociada a este correo.',
            ]);
        }

        // Generar un código de recuperación de 6 dígitos
        $codigoRecuperacion = random_int(100000, 999999);
        $usuario->setCodigoRecuperacion($codigoRecuperacion);

        // Guardar el código en la base de datos
        $entityManager->persist($usuario);
        $entityManager->flush();

        // Enviar correo con el código de recuperación
        $emailMessage = (new Email())
            ->from('no-reply@empresa.com')
            ->to($usuario->getNombreUsuario())
            ->subject('Código de Recuperación de Contraseña')
            ->html("<p>Tu código de recuperación es: <strong>$codigoRecuperacion</strong></p>
                    <p>Ingresa este código en la página de recuperación para restablecer tu contraseña.</p>");

        $mailer->send($emailMessage); // 📩 Enviar correo a Mailtrap

        return $this->render('recuperar_password.html.twig', ['error'=>null,
            'success' => 'Se ha enviado un código de recuperación a tu correo.',
        ]);
    }
    
}
