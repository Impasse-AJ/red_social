<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'usuarios')]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    private $roles = ['ROLE_USER'];
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;
    
    // Cambiar de 'email' a 'nombre_usuario'
    #[ORM\Column(name: 'nombre_usuario', type: 'string', unique: true)]
    private $nombreUsuario;
    
    #[ORM\Column(name: 'contrasena', type: 'string')]
    private $contrasena;

    #[ORM\Column(name: 'fecha_creacion', type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private $fechaCreacion;

    #[ORM\Column(name: 'activo', type: 'boolean')]
    private bool $activo = false;

    // Nuevos campos para recuperación de contraseña
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

    // Cambiar getter y setter de 'email' por 'nombreUsuario'
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
        return array_unique($this->roles);
    }

    public function getPassword(): string
    {
        return $this->contrasena;
    }

    public function getUserIdentifier(): string
    {
        return $this->nombreUsuario;  // Aquí se cambia para usar 'nombre_usuario'
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

    // Getter y Setter para 'activo'
    public function getActivo(): bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): self
    {
        $this->activo = $activo;
        return $this;
    }
}

