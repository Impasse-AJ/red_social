<?php

namespace App\Enum;

/**
 * Estados posibles de una solicitud de amistad.
 * Backed enum (PHP 8.1+): Doctrine persiste el valor string en la columna.
 */
enum EstadoAmistad: string
{
    case Pendiente = 'pendiente';
    case Aceptada = 'aceptada';
    case Rechazada = 'rechazada';
}
