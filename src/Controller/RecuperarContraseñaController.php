<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\Usuario;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function PHPSTORM_META\map;

class RecuperarContraseñaController extends AbstractController
{
    #[Route('/recuperar-password', name: 'recuperar_password')]
    public function mostrarFormulario()
    {
        return $this->render('recuperar_password.html.twig', [
            'success' => null,
            'error' => null
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

        //use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

        // Generar un código de recuperación aleatorio y hashearlo
        $codigoRecuperacion = random_int(100000, 999999);
        $codigoHasheado = md5($codigoRecuperacion);
        $usuario->setCodigoRecuperacion($codigoHasheado);

        // Guardar el código en la base de datos
        $entityManager->persist($usuario);
        $entityManager->flush();

        // Generar la URL absoluta para el restablecimiento de contraseña
        $urlRecuperacion = $this->generateUrl(
            'restablecer_password',
            ['codigo' => $codigoRecuperacion], // Se pasará el código en la URL
            UrlGeneratorInterface::ABSOLUTE_URL // 🔥 Asegura la URL completa
        );

        // Enviar correo con el enlace de recuperación
        $emailMessage = (new Email())
            ->from('no-reply@empresa.com')
            ->to($usuario->getNombreUsuario())
            ->subject('Restablecer tu Contraseña')
            ->html("<p>Haz clic en el siguiente enlace para restablecer tu contraseña:</p>
            <a href='" . $urlRecuperacion . "'>Restablecer Contraseña</a>");

        $mailer->send($emailMessage);


        return $this->render('recuperar_password.html.twig', [
            'error' => null,
            'success' => 'Se ha enviado un código de recuperación a tu correo.',
        ]);
    }
    #[Route('/restablecer-password/{codigo}', name: 'restablecer_password')]
    public function mostrarRestablecerFormulario($codigo, EntityManagerInterface $entityManager)
    {
        // Buscar al usuario con el código de recuperación
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['codigoRecuperacion' => md5($codigo)]);
        // Validar si el código de recuperación es correcto
        if (!$usuario) {
            return $this->render('restablecer_password.html.twig', [
                'error' => 'El código de recuperación es inválido o ya ha sido utilizado.',
                'codigo' => null
            ]);
        }

        return $this->render('restablecer_password.html.twig', [
            'error' => null,
            'codigo' => $codigo // Se enviará al formulario para procesarlo
        ]);
    }

    #[Route('/procesar-restablecimiento', name: 'procesar_restablecimiento', methods: ['POST'])]
    public function procesarRestablecimiento(Request $request, EntityManagerInterface $entityManager)
    {
        $codigo = $request->request->get('codigo');
        $nuevaContrasena = $request->request->get('password');
        // Buscar al usuario con el código de recuperación
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['codigoRecuperacion' => $codigo]);

        if (!$usuario) {
            return $this->render('restablecer_password.html.twig', [
                'error' => 'El código de recuperación es inválido o ya ha sido utilizado.',
                'codigo' => null
            ]);
        }

        $usuario->setContrasena(password_hash($nuevaContrasena, PASSWORD_BCRYPT));
        $usuario->setCodigoRecuperacion(null); // Se elimina el código de recuperación

        $entityManager->flush(); // Guardar cambios en la base de datos

        return $this->redirectToRoute('ctrl_login'); // Redirigir al login
    }
}
