<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'reacciones')]
class Reaccion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Publicacion::class)]
    #[ORM\JoinColumn(name: 'id_publicacion', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $publicacion;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'id_usuario', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $usuario;

    #[ORM\Column(name: 'tipo', type: 'string', length: 255)]
    private $tipo;

    #[ORM\Column(name: 'fecha_creacion', type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private $fechaCreacion;

    public function __construct()
    {
        $this->fechaCreacion = new \DateTime();
    }

    // Getters y Setters
    public function getId() { return $this->id; }

    public function getPublicacion() { return $this->publicacion; }
    public function setPublicacion($publicacion) { $this->publicacion = $publicacion; }

    public function getUsuario() { return $this->usuario; }
    public function setUsuario($usuario) { $this->usuario = $usuario; }

    public function getTipo() { return $this->tipo; }
    public function setTipo($tipo) { $this->tipo = $tipo; }

    public function getFechaCreacion() { return $this->fechaCreacion; }
}
