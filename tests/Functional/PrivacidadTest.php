<?php

namespace App\Tests\Functional;

class PrivacidadTest extends FunctionalTestCase
{
    public function testUnAmigoVeElPerfilYSusPublicaciones(): void
    {
        $client = static::createClient();

        $autor = $this->crearUsuario('autor');
        $amigo = $this->crearUsuario('amigo');
        $this->crearAmistad($autor, $amigo);
        $this->crearPublicacion($autor, 'Contenido visible para amigos');

        $client->loginUser($amigo, 'main');
        $client->request('GET', '/perfil/' . $autor->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.post-content', 'Contenido visible para amigos');
    }

    public function testUnExtranoVeElPerfilPrivadoSinPublicaciones(): void
    {
        $client = static::createClient();

        $autor = $this->crearUsuario('autor');
        $extrano = $this->crearUsuario('extrano');
        $this->crearPublicacion($autor, 'Contenido que no debe verse');

        $client->loginUser($extrano, 'main');
        $client->request('GET', '/perfil/' . $autor->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.empty-state', 'privado');
        $this->assertSelectorNotExists('.post-content');
    }

    public function testUnExtranoNoPuedeVerUnaPublicacionSuelta(): void
    {
        $client = static::createClient();

        $autor = $this->crearUsuario('autor');
        $extrano = $this->crearUsuario('extrano');
        $publicacion = $this->crearPublicacion($autor, 'Publicación protegida');

        $client->loginUser($extrano, 'main');
        $client->request('GET', '/publicacion/' . $publicacion->getId());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testElAutorSiPuedeVerSuPublicacion(): void
    {
        $client = static::createClient();

        $autor = $this->crearUsuario('autor');
        $publicacion = $this->crearPublicacion($autor, 'Mi propia publicación');

        $client->loginUser($autor, 'main');
        $client->request('GET', '/publicacion/' . $publicacion->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.post-content', 'Mi propia publicación');
    }
}
