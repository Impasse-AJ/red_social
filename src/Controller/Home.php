<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class Home extends AbstractController
{
    #[Route('/home', name: 'ctrl_home')]
    public function home(): Response
    {
        // Puedes pasar datos al template si lo necesitas
        return $this->render('home.html.twig', [
            'message' => 'Bienvenido a tu página de inicio.'
        ]);
    }
}
