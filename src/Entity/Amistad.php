<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Usuario;

#[ORM\Entity]
#[ORM\Table(name: 'amistades')]
class Amistad
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'id_solicitante', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Usuario $solicitante;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'id_receptor', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Usuario $receptor;

    #[ORM\Column(type: 'string', length: 10)]
    private string $estado = 'ninguna';

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

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): self
    {
        if (!in_array($estado, ['niguna','pendiente', 'aceptada', 'rechazada'])) {
            throw new \InvalidArgumentException("Estado de amistad inválido.");
        }

        $this->estado = $estado;
        return $this;
    }
}
