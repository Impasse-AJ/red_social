<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Repository\AmistadRepository;
use App\Repository\PublicacionRepository;
use App\Repository\UsuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class Home extends AbstractController
{
    #[Route('/home', name: 'ctrl_home')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function home(AmistadRepository $amistades, PublicacionRepository $publicaciones): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        // El feed incluye las publicaciones propias y las de los amigos
        $amigos = $amistades->amigosDe($usuario);
        $autores = [$usuario, ...$amigos];

        return $this->render('home.html.twig', [
            'usuario' => $usuario,
            'publicaciones' => $publicaciones->feedDe($autores),
            'amigos' => count($amigos),
        ]);
    }

    #[Route('/descubrir', name: 'descubrir')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function descubrir(Request $request, UsuarioRepository $usuarios): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();
        $busqueda = trim((string) $request->query->get('q'));

        return $this->render('descubrir.html.twig', [
            'usuarios' => $usuarios->buscarActivos($usuario, $busqueda),
            'busqueda' => $busqueda,
        ]);
    }
}
