<?php

namespace App\Tests\Functional;

use App\Entity\Usuario;

class RegistroTest extends FunctionalTestCase
{
    public function testElRegistroCreaUnaCuentaInactivaConToken(): void
    {
        $client = static::createClient();
        $sufijo = uniqid();

        $crawler = $client->request('GET', '/registro');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Registrarse')->form([
            'email' => "registro_{$sufijo}@test.local",
            'nombre_usuario' => "registro_{$sufijo}",
            'contrasena' => 'ContrasenaValida123',
        ]);
        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-success', 'correo de confirmación');

        $usuario = $this->em()->getRepository(Usuario::class)
            ->findOneBy(['email' => "registro_{$sufijo}@test.local"]);

        $this->assertNotNull($usuario);
        $this->assertFalse($usuario->getActivo());
        $this->assertNotNull($usuario->getTokenActivacion());
    }

    public function testElEnlaceDeActivacionActivaLaCuentaYEsDeUnSoloUso(): void
    {
        $client = static::createClient();
        $usuario = $this->crearUsuario('activar', activo: false);
        $usuario->setTokenActivacion(bin2hex(random_bytes(32)));
        $this->em()->flush();

        $token = $usuario->getTokenActivacion();

        $client->request('GET', '/activar/' . $token);
        $this->assertResponseRedirects('/login');

        $this->em()->refresh($usuario);
        $this->assertTrue($usuario->getActivo());
        $this->assertNull($usuario->getTokenActivacion());

        // Reutilizar el enlace debe fallar
        $client->request('GET', '/activar/' . $token);
        $this->assertResponseStatusCodeSame(404);
    }

    public function testNoSePuedeRegistrarUnaContrasenaCorta(): void
    {
        $client = static::createClient();
        $sufijo = uniqid();

        $crawler = $client->request('GET', '/registro');
        $form = $crawler->selectButton('Registrarse')->form([
            'email' => "corta_{$sufijo}@test.local",
            'nombre_usuario' => "corta_{$sufijo}",
            'contrasena' => 'corta',
        ]);
        $client->submit($form);

        $this->assertSelectorTextContains('.alert-error', 'al menos 8 caracteres');
        $this->assertNull(
            $this->em()->getRepository(Usuario::class)->findOneBy(['email' => "corta_{$sufijo}@test.local"])
        );
    }
}
