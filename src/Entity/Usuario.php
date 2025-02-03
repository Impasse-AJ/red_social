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

    #[ORM\Column(name: 'codigo_recuperacion', type: 'string', nullable: true)]
    private $codigoRecuperacion;

    public function __construct()
    {
        $this->fechaCreacion = new \DateTime();
    }

    // Getters y Setters
    public function getId() { return $this->id; }
    
    public function getNombreUsuario() { return $this->nombreUsuario; }
    public function setNombreUsuario($nombreUsuario) { $this->nombreUsuario = $nombreUsuario; }

    public function getContrasena() { return $this->contrasena; }
    public function setContrasena($contrasena) { $this->contrasena = $contrasena; }

    public function getFechaCreacion() { return $this->fechaCreacion; }

    public function getCodigoRecuperacion() { return $this->codigoRecuperacion; }
    public function setCodigoRecuperacion($codigoRecuperacion) { $this->codigoRecuperacion = $codigoRecuperacion; }

    // Métodos de autenticación
    public function getRoles(): array { return ['ROLE_USER']; }
    public function getPassword(): string { return $this->contrasena; }
    public function getUserIdentifier(): string { return $this->nombreUsuario; }
    public function eraseCredentials(): void {}
}
