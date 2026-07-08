<?php
namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
#[ORM\Table(name: 'usuarios')]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'email', type: 'string', unique: true)]
    #[Assert\NotBlank(message: 'El correo electrónico es obligatorio.')]
    #[Assert\Email(message: 'El correo electrónico no es válido.')]
    #[Assert\Length(max: 180, maxMessage: 'El correo electrónico es demasiado largo.')]
    private string $email;

    #[ORM\Column(name: 'nombre_usuario', type: 'string', unique: true)]
    #[Assert\NotBlank(message: 'El nombre de usuario es obligatorio.')]
    #[Assert\Length(min: 3, max: 50, minMessage: 'El nombre de usuario debe tener al menos {{ limit }} caracteres.', maxMessage: 'El nombre de usuario no puede superar {{ limit }} caracteres.')]
    #[Assert\Regex(pattern: '/^[a-zA-Z0-9_.-]+$/', message: 'El nombre de usuario solo puede contener letras, números, puntos, guiones y guiones bajos.')]
    private string $nombreUsuario;

    #[ORM\Column(name: 'contrasena', type: 'string')]
    private string $contrasena;
    #[ORM\Column(name: 'foto_perfil', type: 'string', nullable: true)]
    private ?string $fotoPerfil = null;

    #[ORM\Column(name: 'fecha_creacion', type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTime $fechaCreacion;

    #[ORM\Column(name: 'codigo_recuperacion', type: 'string', nullable: true)]
    private ?string $codigoRecuperacion = null;

    #[ORM\Column(name: 'codigo_recuperacion_expira', type: 'datetime', nullable: true)]
    private ?\DateTime $codigoRecuperacionExpira = null;

    #[ORM\Column(name: 'token_activacion', type: 'string', length: 64, nullable: true)]
    private ?string $tokenActivacion = null;

    #[ORM\Column(name: 'activo', type: 'boolean')]
    private bool $activo = false;

    public function __construct()
    {
        $this->fechaCreacion = new \DateTime();
        $this->fotoPerfil = 'default-profile-picture.jpg';
    }

    // Getters y Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFotoPerfil(): ?string
    {
        return $this->fotoPerfil;
    }
    public function setFotoPerfil(?string $fotoPerfil): self
    {
        $this->fotoPerfil = $fotoPerfil;
        return $this;
    }
    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
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

    public function getCodigoRecuperacion(): ?string
    {
        return $this->codigoRecuperacion;
    }

    public function setCodigoRecuperacion(?string $codigoRecuperacion): self
    {
        $this->codigoRecuperacion = $codigoRecuperacion;
        return $this;
    }

    public function getCodigoRecuperacionExpira(): ?\DateTime
    {
        return $this->codigoRecuperacionExpira;
    }

    public function setCodigoRecuperacionExpira(?\DateTime $codigoRecuperacionExpira): self
    {
        $this->codigoRecuperacionExpira = $codigoRecuperacionExpira;
        return $this;
    }

    public function getTokenActivacion(): ?string
    {
        return $this->tokenActivacion;
    }

    public function setTokenActivacion(?string $tokenActivacion): self
    {
        $this->tokenActivacion = $tokenActivacion;
        return $this;
    }

    public function getActivo(): bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): self
    {
        $this->activo = $activo;
        return $this;
    }

    // Métodos de autenticación
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
        return $this->email;
    }

    public function eraseCredentials(): void {}
}
