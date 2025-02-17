<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
#[ORM\Table(name: 'amistad')]
class Amistad
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'usuario_a_id', referencedColumnName: 'id', nullable: false)]
    private $usuarioA;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'usuario_b_id', referencedColumnName: 'id', nullable: false)]
    private $usuarioB;

    #[ORM\Column(type: 'boolean')]
    private $aceptada = false;

    #[ORM\Column(type: 'datetime')]
    private $fechaSolicitud;

    public function __construct()
    {
        $this->fechaSolicitud = new \DateTime(); // Inicializa con la fecha actual
    }

    // Getters y setters
    public function getUsuarioA(): ?Usuario
    {
        return $this->usuarioA;
    }

    public function setUsuarioA(Usuario $usuarioA): self
    {
        $this->usuarioA = $usuarioA;
        return $this;
    }

    public function getUsuarioB(): ?Usuario
    {
        return $this->usuarioB;
    }

    public function setUsuarioB(Usuario $usuarioB): self
    {
        $this->usuarioB = $usuarioB;
        return $this;
    }

    public function isAceptada(): bool
    {
        return $this->aceptada;
    }

    public function setAceptada(bool $aceptada): self
    {
        $this->aceptada = $aceptada;
        return $this;
    }

    public function getFechaSolicitud(): \DateTime
    {
        return $this->fechaSolicitud;
    }

    public function setFechaSolicitud(\DateTime $fechaSolicitud): self
    {
        $this->fechaSolicitud = $fechaSolicitud;
        return $this;
    }
}
