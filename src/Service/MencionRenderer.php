<?php

namespace App\Service;

use App\Repository\UsuarioRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Convierte las menciones @usuario de un texto en enlaces al perfil.
 * El texto se escapa aquí (el filtro Twig lo marca como seguro), así que
 * el contenido del usuario nunca puede inyectar HTML.
 */
class MencionRenderer
{
    // Nombres válidos: letras/números/_ con puntos o guiones intermedios (no al final)
    private const PATRON = '/@([a-zA-Z0-9_]+(?:[.-][a-zA-Z0-9_]+)*)/';

    public function __construct(
        private UsuarioRepository $usuarios,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function aHtml(string $texto): string
    {
        $escapado = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');

        $resultado = preg_replace_callback(self::PATRON, function (array $coincidencia): string {
            $usuario = $this->usuarios->porNombreUsuario($coincidencia[1]);

            // Si no existe el usuario, la mención se queda como texto plano
            if (!$usuario) {
                return $coincidencia[0];
            }

            $url = $this->urlGenerator->generate('ver_perfil', ['id' => $usuario->getId()]);

            return '<a class="mention" href="' . $url . '">@' . htmlspecialchars($usuario->getNombreUsuario(), ENT_QUOTES, 'UTF-8') . '</a>';
        }, $escapado);

        return $resultado ?? $escapado;
    }
}
