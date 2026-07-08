<?php

namespace App\Tests\Functional;

use App\Entity\Amistad;
use App\Entity\Publicacion;
use App\Entity\Usuario;
use App\Enum\EstadoAmistad;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Base para tests funcionales: helpers para crear datos de prueba únicos.
 */
abstract class FunctionalTestCase extends WebTestCase
{
    public const PASSWORD = 'PasswordDeTest123';

    protected function em(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    protected function crearUsuario(string $prefijo = 'user', bool $activo = true): Usuario
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $sufijo = uniqid();

        $usuario = new Usuario();
        $usuario->setNombreUsuario($prefijo . '_' . $sufijo);
        $usuario->setEmail($prefijo . '_' . $sufijo . '@test.local');
        $usuario->setContrasena($hasher->hashPassword($usuario, self::PASSWORD));
        $usuario->setActivo($activo);

        $em = $this->em();
        $em->persist($usuario);
        $em->flush();

        return $usuario;
    }

    protected function crearAmistad(Usuario $solicitante, Usuario $receptor, EstadoAmistad $estado = EstadoAmistad::Aceptada): Amistad
    {
        $amistad = new Amistad();
        $amistad->setSolicitante($solicitante);
        $amistad->setReceptor($receptor);
        $amistad->setEstado($estado);

        $em = $this->em();
        $em->persist($amistad);
        $em->flush();

        return $amistad;
    }

    protected function crearPublicacion(Usuario $autor, string $contenido): Publicacion
    {
        $publicacion = new Publicacion();
        $publicacion->setUsuario($autor);
        $publicacion->setContenido($contenido);
        $publicacion->setFechaCreacion(new \DateTime());

        $em = $this->em();
        $em->persist($publicacion);
        $em->flush();

        return $publicacion;
    }
}
