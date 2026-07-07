<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LandingController extends AbstractController
{
    #[Route('/', name: 'landing')]
    public function landing(): Response
    {
        // Un usuario con sesión va directo a su feed
        if ($this->getUser()) {
            return $this->redirectToRoute('ctrl_home');
        }

        return $this->render('landing.html.twig');
    }
}
