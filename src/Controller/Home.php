<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Response;

class Home extends AbstractController
{
    #[Route('/home', name: 'ctrl_home')]
    #[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')] // 🚀 Solo usuarios autenticados pueden ver la lista

    public function home(): Response
    {
        // Puedes pasar datos al template si lo necesitas
        return $this->render('home.html.twig', [
            'mensaje' => 'Bienvenido a tu página de inicio.'
        ]);
    }
}
