<?php

namespace App\Controller;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UsuariosController extends AbstractController
{
    #[Route('/usuarios', name: 'listar_usuarios')]
    #[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')] // 🚀 Solo usuarios autenticados pueden ver la lista
    public function listarUsuarios(EntityManagerInterface $entityManager): Response
    {
        // Obtener todos los usuarios de la base de datos
        $usuarios = $entityManager->getRepository(Usuario::class)->findAll();

        // Renderizar la plantilla y enviar los usuarios
        return $this->render('usuarios.html.twig', [
            'mensaje' => 'Bienvenido a tu página de inicio.',
            'usuarios' => $usuarios
        ]);
    }
}
