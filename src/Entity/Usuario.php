<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'usuarios')]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(name: 'email', type: 'string', unique: true)]
    private string $email;

    #[ORM\Column(name: 'nombre_usuario', type: 'string', unique: true)]
    private string $nombreUsuario;

    #[ORM\Column(name: 'contrasena', type: 'string')]
    private string $contrasena;

    #[ORM\Column(name: 'foto_perfil', type: 'string', nullable: true)]
    private ?string $fotoPerfil = null;

    #[ORM\Column(name: 'fecha_creacion', type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTime $fechaCreacion;

    #[ORM\Column(name: 'codigo_recuperacion', type: 'string', nullable: true)]
    private ?string $codigoRecuperacion = null;

    #[ORM\Column(name: 'activo', type: 'boolean')]
    private bool $activo = false;

    // Relación de amistades
    #[ORM\OneToMany(mappedBy: 'usuarioA', targetEntity: Amistad::class)]
    private Collection $amistadesEnviadas;

    #[ORM\OneToMany(mappedBy: 'usuarioB', targetEntity: Amistad::class)]
    private Collection $amistadesRecibidas;

    public function __construct()
    {
        $this->fechaCreacion = new \DateTime();
        $this->fotoPerfil = 'default-profile-picture.jpg';
        $this->amistadesEnviadas = new ArrayCollection();
        $this->amistadesRecibidas = new ArrayCollection();
    }

    // Getters y Setters para amistades
    public function getAmistadesEnviadas(): Collection
    {
        return $this->amistadesEnviadas;
    }

    public function getAmistadesRecibidas(): Collection
    {
        return $this->amistadesRecibidas;
    }

    // Otros getters y setters permanecen igual...
    public function getId(): ?int
    {
        return $this->id;
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

    public function getFotoPerfil(): ?string
    {
        return $this->fotoPerfil;
    }

    public function setFotoPerfil(?string $fotoPerfil): self
    {
        $this->fotoPerfil = $fotoPerfil;
        return $this;
    }

    public function getFechaCreacion(): \DateTime
    {
        return $this->fechaCreacion;
    }
        public function estaAmistadEnviada(Usuario $usuario): bool
    {
        return $this->amistadesEnviadas->exists(fn($key, $amistad) => $amistad->getUsuarioB() === $usuario && !$amistad->isAceptada());
    }

    public function estaAmistadRecibida(Usuario $usuario): bool
    {
    return $this->amistadesRecibidas->exists(fn($key, $amistad) => $amistad->getUsuarioA() === $usuario && !$amistad->isAceptada());
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
