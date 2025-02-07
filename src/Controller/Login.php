<?php 
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
class Login extends AbstractController
{
    #[Route('/login', name: 'ctrl_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Verifica si hubo algún error durante la autenticación
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Recupera el último nombre de usuario que se intentó
        $lastEmail = $authenticationUtils->getLastUsername();

    

        // Renderiza la vista de login con el último nombre de usuario y cualquier error
        return $this->render('login.html.twig', [
            'last_email' => $lastEmail,
            'error' => $error
        ]);
    } 
    
	
	#[Route('/logout', name:'ctrl_logout')]
    public function logout(){    
        return new Response();
    }    
}