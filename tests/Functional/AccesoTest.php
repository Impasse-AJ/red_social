<?php

namespace App\Tests\Functional;

class AccesoTest extends FunctionalTestCase
{
    public function testLaLandingEsPublica(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'SymSocial');
    }

    public function testElLoginEsPublico(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
    }

    public function testElHomeRequiereSesion(): void
    {
        $client = static::createClient();
        $client->request('GET', '/home');

        $this->assertResponseRedirects('http://localhost/login');
    }

    public function testUnUsuarioAutenticadoVeSuFeed(): void
    {
        $client = static::createClient();
        $usuario = $this->crearUsuario('feed');

        $client->loginUser($usuario, 'main');
        $client->request('GET', '/home');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Inicio');
    }
}
