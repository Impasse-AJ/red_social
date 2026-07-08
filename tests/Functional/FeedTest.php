<?php

namespace App\Tests\Functional;

use App\Enum\EstadoAmistad;

class FeedTest extends FunctionalTestCase
{
    public function testElFeedMuestraPublicacionesPropiasYDeAmigos(): void
    {
        $client = static::createClient();

        $usuario = $this->crearUsuario('yo');
        $amigo = $this->crearUsuario('amigo');
        $this->crearAmistad($amigo, $usuario); // amistad en sentido inverso: también cuenta

        $this->crearPublicacion($usuario, 'Publicación propia en el feed');
        $this->crearPublicacion($amigo, 'Publicación del amigo en el feed');

        $client->loginUser($usuario, 'main');
        $crawler = $client->request('GET', '/home');

        $this->assertResponseIsSuccessful();
        $contenido = $crawler->filter('main')->text();
        $this->assertStringContainsString('Publicación propia en el feed', $contenido);
        $this->assertStringContainsString('Publicación del amigo en el feed', $contenido);
    }

    public function testElFeedNoMuestraPublicacionesDeDesconocidos(): void
    {
        $client = static::createClient();

        $usuario = $this->crearUsuario('yo');
        $desconocido = $this->crearUsuario('desconocido');
        $this->crearPublicacion($desconocido, 'Esto no debería aparecer');

        $client->loginUser($usuario, 'main');
        $crawler = $client->request('GET', '/home');

        $this->assertResponseIsSuccessful();
        $this->assertStringNotContainsString('Esto no debería aparecer', $crawler->filter('main')->text());
    }

    public function testUnaSolicitudPendienteNoDaAccesoAlFeed(): void
    {
        $client = static::createClient();

        $usuario = $this->crearUsuario('yo');
        $pendiente = $this->crearUsuario('pendiente');
        $this->crearAmistad($pendiente, $usuario, EstadoAmistad::Pendiente);
        $this->crearPublicacion($pendiente, 'Aún no somos amigos');

        $client->loginUser($usuario, 'main');
        $crawler = $client->request('GET', '/home');

        $this->assertResponseIsSuccessful();
        $this->assertStringNotContainsString('Aún no somos amigos', $crawler->filter('main')->text());
    }
}
