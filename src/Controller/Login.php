<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class Login extends AbstractController
{
    #[Route('/login', name: 'ctrl_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Último error de autenticación y último email introducido
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastEmail = $authenticationUtils->getLastUsername();

        return $this->render('login.html.twig', [
            'last_email' => $lastEmail,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'ctrl_logout')]
    public function logout(): never
    {
        // El firewall intercepta esta ruta; este código nunca se ejecuta
        throw new \LogicException('Esta ruta la gestiona el firewall de Symfony.');
    }
}
