<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Usuario;

class Home extends AbstractController
{
    #[Route('/home', name: 'ctrl_home')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // 🔐 Solo usuarios autenticados pueden acceder
    public function home(EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser(); // Obtener usuario autenticado
        $usuarios = $entityManager->getRepository(Usuario::class)->findAll(); // Obtener lista de usuarios

        return $this->render('home.html.twig', [
            'usuario' => $usuario,
            'usuarios' => $usuarios,
        ]);
    }
}
