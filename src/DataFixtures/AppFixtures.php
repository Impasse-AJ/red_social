<?php

namespace App\DataFixtures;

use App\Entity\Amistad;
use App\Entity\Comentario;
use App\Entity\Publicacion;
use App\Entity\Usuario;
use App\Enum\EstadoAmistad;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Datos de demostración para desarrollo y pruebas.
 * Todos los usuarios comparten la contraseña "symsocial123".
 */
class AppFixtures extends Fixture
{
    public const PASSWORD = 'symsocial123';

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // ----- Usuarios -----
        $definiciones = [
            ['abraham123', 'abraham@social.com'],
            ['lucia_dev', 'lucia@example.com'],
            ['marco92', 'marco@example.com'],
            ['sara_m', 'sara@example.com'],
            ['alex_demo', 'alex@example.com'],
        ];

        /** @var array<string, Usuario> $usuarios */
        $usuarios = [];

        foreach ($definiciones as [$nombre, $email]) {
            $usuario = new Usuario();
            $usuario->setNombreUsuario($nombre);
            $usuario->setEmail($email);
            $usuario->setContrasena($this->passwordHasher->hashPassword($usuario, self::PASSWORD));
            $usuario->setActivo(true);

            $manager->persist($usuario);
            $usuarios[$nombre] = $usuario;
        }

        // ----- Amistades -----
        $this->crearAmistad($manager, $usuarios['abraham123'], $usuarios['lucia_dev'], EstadoAmistad::Aceptada);
        $this->crearAmistad($manager, $usuarios['marco92'], $usuarios['abraham123'], EstadoAmistad::Aceptada);
        // sara_m tiene una solicitud pendiente hacia abraham123 (para probar el badge)
        $this->crearAmistad($manager, $usuarios['sara_m'], $usuarios['abraham123'], EstadoAmistad::Pendiente);
        $this->crearAmistad($manager, $usuarios['lucia_dev'], $usuarios['marco92'], EstadoAmistad::Aceptada);
        $this->crearAmistad($manager, $usuarios['lucia_dev'], $usuarios['sara_m'], EstadoAmistad::Aceptada);
        $this->crearAmistad($manager, $usuarios['marco92'], $usuarios['sara_m'], EstadoAmistad::Aceptada);
        // alex_demo tiene una solicitud pendiente hacia lucia_dev (para probar el badge)
        $this->crearAmistad($manager, $usuarios['alex_demo'], $usuarios['lucia_dev'], EstadoAmistad::Pendiente);

        // ----- Publicaciones -----
        $publicaciones = [
            ['lucia_dev', '¡Por fin viernes! ¿Alguien se apunta a una ruta este finde?', '-2 hours'],
            ['marco92', 'Acabo de terminar mi primer proyecto en Symfony. Qué gustazo verlo funcionando en producción.', '-1 day'],
            ['sara_m', 'Foto nueva de perfil. Se aceptan opiniones (amables).', '-3 days'],
            ['lucia_dev', 'Configurando mi entorno de desarrollo nuevo. Docker + Symfony CLI y a volar.', '-5 days'],
        ];

        $primeraPublicacion = null;

        foreach ($publicaciones as [$autor, $contenido, $cuando]) {
            $publicacion = new Publicacion();
            $publicacion->setUsuario($usuarios[$autor]);
            $publicacion->setContenido($contenido);
            $publicacion->setFechaCreacion(new \DateTime($cuando));

            $manager->persist($publicacion);
            $primeraPublicacion ??= $publicacion;
        }

        // ----- Comentarios en la primera publicación -----
        $comentarios = [
            ['marco92', '¡Yo me apunto! ¿A qué hora salimos?', '-90 minutes'],
            ['sara_m', 'Si es por la mañana, contad conmigo.', '-45 minutes'],
        ];

        foreach ($comentarios as [$autor, $contenido, $cuando]) {
            $comentario = new Comentario();
            $comentario->setPublicacion($primeraPublicacion);
            $comentario->setUsuario($usuarios[$autor]);
            $comentario->setContenido($contenido);
            $comentario->setFechaCreacion(new \DateTime($cuando));

            $manager->persist($comentario);
        }

        $manager->flush();
    }

    private function crearAmistad(ObjectManager $manager, Usuario $solicitante, Usuario $receptor, EstadoAmistad $estado): void
    {
        $amistad = new Amistad();
        $amistad->setSolicitante($solicitante);
        $amistad->setReceptor($receptor);
        $amistad->setEstado($estado);

        $manager->persist($amistad);
    }
}
