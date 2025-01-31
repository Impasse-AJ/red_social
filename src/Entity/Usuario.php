<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'usuarios')]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(name: 'nombre_usuario', type: 'string', unique: true)]
    private $nombreUsuario;
 
    #[ORM\Column(name: 'contrasena', type: 'string')]
    private $contrasena;

    #[ORM\Column(name: 'fecha_creacion', type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private $fechaCreacion;

    // NUEVOS CAMPOS PARA RECUPERACIÓN DE CONTRASEÑA
    #[ORM\Column(name: 'token_recuperacion', type: 'string', nullable: true)]
    private ?string $tokenRecuperacion = null;

    #[ORM\Column(name: 'expiracion_token', type: 'datetime', nullable: true)]
    private ?\DateTime $expiracionToken = null;

    public function __construct()
    {
        $this->fechaCreacion = new \DateTime();
    }

    // Getters y Setters
    public function getId(): int
    {
        return $this->id;
    }

    public function getNombreUsuario(): string
    {
        return $this->nombreUsuario;
    }

    public function setNombreUsuario(string $nombreUsuario): self
    {
        $this->nombreUsuario = $nombreUsuario;
        return $this;
    }

    public function getContrasena(): string
    {
        return $this->contrasena;
    }

    public function setContrasena(string $contrasena): self
    {
        $this->contrasena = $contrasena;
        return $this;
    }

    public function getFechaCreacion(): \DateTime
    {
        return $this->fechaCreacion;
    }

    // Métodos de autenticación para Symfony Security
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getPassword(): string
    {
        return $this->contrasena;
    }

    public function getUserIdentifier(): string
    {
        return $this->nombreUsuario;
    }

    public function eraseCredentials(): void {}

    // MÉTODOS PARA RECUPERACIÓN DE CONTRASEÑA
    public function getTokenRecuperacion(): ?string
    {
        return $this->tokenRecuperacion;
    }

    public function setTokenRecuperacion(?string $token): self
    {
        $this->tokenRecuperacion = $token;
        return $this;
    }

    public function getExpiracionToken(): ?\DateTime
    {
        return $this->expiracionToken;
    }

    public function setExpiracionToken(?\DateTime $expiracionToken): self
    {
        $this->expiracionToken = $expiracionToken;
        return $this;
    }
}
