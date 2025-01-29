<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'publicaciones')]
class Publicacion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'id_usuario', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $usuario;

    #[ORM\Column(name: 'contenido', type: 'string', length: 255)]
    private $contenido;

    #[ORM\Column(name: 'fecha_creacion', type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private $fechaCreacion;

    public function __construct()
    {
        $this->fechaCreacion = new \DateTime();
    }

    // Getters y Setters
    public function getId() { return $this->id; }

    public function getUsuario() { return $this->usuario; }
    public function setUsuario($usuario) { $this->usuario = $usuario; }

    public function getContenido() { return $this->contenido; }
    public function setContenido($contenido) { $this->contenido = $contenido; }

    public function getFechaCreacion() { return $this->fechaCreacion; }
}
