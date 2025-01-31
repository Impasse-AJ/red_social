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

class RegistroController extends AbstractController
{
    #[Route('/registro', name: 'registro')]
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer, ValidatorInterface $validator): Response
    {
        if ($request->isMethod('POST')) {
            $nombreUsuario = $request->request->get('nombre_usuario');
            $contrasena = $request->request->get('contrasena');
            $email = $request->request->get('email');
            
            $usuario = new Usuario();
            $usuario->setNombreUsuario($nombreUsuario);
            $usuario->setContrasena($passwordHasher->hashPassword($usuario, $contrasena));
            
            $errors = $validator->validate($usuario);
            if (count($errors) > 0) {
                return $this->render('registro.html.twig', ['errors' => $errors]);
            }
            
            $usuario->setFechaCreacion(new \DateTime());
            $usuario->setRoles(['ROLE_USER']);
            $usuario->setActivacionToken(bin2hex(random_bytes(32)));
            
            $em->persist($usuario);
            $em->flush();
            
            // Enviar email de activación
            $emailMessage = (new Email())
                ->from('noreply@redsocial.com')
                ->to($email)
                ->subject('Activa tu cuenta')
                ->html('<p>Hola, activa tu cuenta haciendo clic en el siguiente enlace: </p>
                       <a href="' . $this->generateUrl('activar_cuenta', ['token' => $usuario->getActivacionToken()], 0) . '">Activar Cuenta</a>');
            
            $mailer->send($emailMessage);
            
            return $this->redirectToRoute('mensaje_activacion');
        }
        
        return $this->render('registro.html.twig');
    }
    
    #[Route('/activar/{token}', name: 'activar_cuenta')]
    public function activarCuenta(string $token, EntityManagerInterface $em): Response
    {
        $usuario = $em->getRepository(Usuario::class)->findOneBy(['activacionToken' => $token]);
        
        if (!$usuario) {
            throw $this->createNotFoundException('Token inválido.');
        }
        
        $usuario->setActivacionToken(null);
        $em->flush();
        
        return $this->redirectToRoute('ctrl_login');
    }
}