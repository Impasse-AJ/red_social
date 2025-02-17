<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Usuario;
use App\Entity\Amistad;

class Home extends AbstractController
{
    #[Route('/home', name: 'ctrl_home')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // 🔐 Solo usuarios autenticados pueden acceder
    public function home(EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser(); // Obtiene el usuario autenticado

        if (!$usuario) {
            return $this->redirectToRoute('ctrl_login'); // Redirige si no está autenticado
        }

        // Obtener todos los usuarios excepto el usuario actual
        $usuarios = $entityManager->getRepository(Usuario::class)->findAll();

        // Contar solicitudes de amistad pendientes para este usuario
        $solicitudesPendientes = $entityManager->getRepository(Amistad::class)->count([
            'receptor' => $usuario,
            'estado' => 'pendiente'
        ]);

        return $this->render('home.html.twig', [
            'usuario' => $usuario,
            'usuarios' => $usuarios,
            'solicitudesPendientes' => $solicitudesPendientes, // Enviar a la vista
        ]);
    }
}
