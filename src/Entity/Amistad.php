<?php
namespace App\Entity;

use App\Enum\EstadoAmistad;
use App\Repository\AmistadRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AmistadRepository::class)]
#[ORM\Table(name: 'amistades')]
class Amistad
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'id_solicitante', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Usuario $solicitante;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'id_receptor', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Usuario $receptor;

    #[ORM\Column(type: 'string', length: 10, enumType: EstadoAmistad::class)]
    private EstadoAmistad $estado = EstadoAmistad::Pendiente;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSolicitante(): Usuario
    {
        return $this->solicitante;
    }

    public function setSolicitante(Usuario $solicitante): self
    {
        $this->solicitante = $solicitante;
        return $this;
    }

    public function getReceptor(): Usuario
    {
        return $this->receptor;
    }

    public function setReceptor(Usuario $receptor): self
    {
        $this->receptor = $receptor;
        return $this;
    }

    public function getEstado(): EstadoAmistad
    {
        return $this->estado;
    }

    public function setEstado(EstadoAmistad $estado): self
    {
        $this->estado = $estado;
        return $this;
    }
}
