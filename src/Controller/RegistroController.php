<?php 


namespace App\Controller;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RegistroController extends AbstractController
{
    #[Route('/registro', name: 'registro')]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer, ValidatorInterface $validator): Response
    {
        if ($request->isMethod('POST')) {
            // Obtener datos del formulario
            $nombreUsuario = $request->request->get('nombre_usuario');
            $contrasena = $request->request->get('contrasena');
            
            // Validar datos
            $constraints = new Assert\Collection([
                'nombre_usuario' => [
                    new Assert\NotBlank()
                   
                   
                ],
                
            ]);
            
            $violations = $validator->validate([
                'nombre_usuario' => $nombreUsuario,
                'contrasena' => $contrasena
            ], $constraints);

            if (count($violations) > 0) {
                // Si hay errores de validación
                return $this->render('registro.html.twig', ['errors' => $violations]);
            }

            // Verificar si el nombre de usuario ya existe
            $usuarioExistente = $em->getRepository(Usuario::class)->findOneBy(['nombreUsuario' => $nombreUsuario]);
            if ($usuarioExistente) {
                // Si el nombre de usuario ya existe, mostrar error
                return $this->render('registro.html.twig', ['error' => 'El nombre de usuario ya está registrado.']);
            }

            // Crear nuevo usuario
            $usuario = new Usuario();
            $usuario->setNombreUsuario($nombreUsuario);
            $usuario->setContrasena($passwordHasher->hashPassword($usuario, $contrasena));
            $usuario->setRoles(['ROLE_USER']);
            $usuario->setActivo(false);
            
            // Persistir el usuario
            $em->persist($usuario);
            $em->flush();
            
            // Enviar email de activación
            $emailMessage = (new Email())
                ->from('noreply@redsocial.com')
                ->to($nombreUsuario . '@redsocial.com') // Asegúrate de que se envíe al correo relacionado
                ->subject('Activa tu cuenta')
                ->html('<p>Hola, activa tu cuenta haciendo clic en el siguiente enlace: </p>
                       <a href="' . $this->generateUrl('activar_cuenta', ['id' => $usuario->getId()], 0) . '">Activar Cuenta</a>');
            
            $mailer->send($emailMessage);
            
            return $this->redirectToRoute('mensaje_activacion');
        }
        
        return $this->render('registro.html.twig');
    }

    #[Route('/activar/{id}', name: 'activar_cuenta')]
    public function activarCuenta(int $id, EntityManagerInterface $em): Response
    {
        $usuario = $em->getRepository(Usuario::class)->find($id);
        
        if (!$usuario || $usuario->getActivo()) {
            throw $this->createNotFoundException('Cuenta inválida o ya activada.');
        }
        
        // Activar la cuenta del usuario
        $usuario->setActivo(true);
        $em->flush();
        
        return $this->redirectToRoute('ctrl_login');
    }
}

