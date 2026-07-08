<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Repository\AmistadRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MencionController extends AbstractController
{
    /**
     * Sugerencias de mención para el autocompletado con @:
     * devuelve los amigos del usuario cuyo nombre empieza por lo tecleado.
     */
    #[Route('/menciones/sugerencias', name: 'menciones_sugerencias', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function sugerencias(Request $request, AmistadRepository $amistades): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();
        $busqueda = mb_strtolower(trim((string) $request->query->get('q')));

        $sugerencias = [];

        foreach ($amistades->amigosDe($usuario) as $amigo) {
            $nombre = $amigo->getNombreUsuario();

            if ($busqueda !== '' && !str_starts_with(mb_strtolower($nombre), $busqueda)) {
                continue;
            }

            $foto = $amigo->getFotoPerfil();
            $sugerencias[] = [
                'nombre' => $nombre,
                'avatar' => ($foto && $foto !== 'default-profile-picture.jpg')
                    ? '/uploads/' . $foto
                    : '/img/default-avatar.svg',
            ];

            if (count($sugerencias) >= 5) {
                break;
            }
        }

        return $this->json($sugerencias);
    }
}
