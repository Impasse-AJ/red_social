SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO"; 
SET time_zone = "+00:00";

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS redsocial;
USE redsocial;

-- Estructura de tabla para la tabla `usuarios`
CREATE TABLE `usuarios` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `nombre_usuario` VARCHAR(50) NOT NULL UNIQUE, -- Nueva columna para el apodo del usuario
  `contrasena` VARCHAR(255) NOT NULL,
  `foto_perfil` VARCHAR(255) NOT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `codigo_recuperacion` VARCHAR(255) NULL, -- Nueva columna para almacenar el código de recuperación
  `activo` BOOLEAN DEFAULT FALSE, -- Nueva columna para activar o desactivar la cuenta
  PRIMARY KEY (`id`)
);

-- Estructura de tabla para la tabla `publicaciones`
CREATE TABLE `publicaciones` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `contenido` VARCHAR(255) NOT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
);

-- Estructura de tabla para la tabla `comentarios`
CREATE TABLE `comentarios` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_publicacion` INT NOT NULL,
  `id_usuario` INT NOT NULL,
  `contenido` TEXT NOT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_publicacion`) REFERENCES `publicaciones` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
);

-- Estructura de tabla para la tabla `amistades`
CREATE TABLE amistades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_solicitante INT NOT NULL,
    id_receptor INT NOT NULL,
    estado ENUM('ninguna','pendiente', 'aceptada', 'rechazada') NOT NULL DEFAULT 'ninguna',
    FOREIGN KEY (id_solicitante) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_receptor) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Insertar usuarios de prueba con contraseña hasheada (1234) y cuentas activadas
INSERT INTO `usuarios` (`email`, `nombre_usuario`, `contrasena`, `activo`) VALUES
('abraham@social.com', 'abraham123', '$2y$10$GAg870uvPLRNCbm8LFez4eg0pJz7Z8.ABVYoLRqIk00tWR8Ef5T/O', TRUE), 
('alejo@social.com', 'alejo_92', '$2y$10$f/0Xle4yDhedSaSR8vPrYOlgBNkhi2PrsQe83Y3.nVa6tb6ClQcte', TRUE), 
('alex@social.com', 'alex_xd', '$2y$10$XAj1J5.1OSZCad9eGg6/P.Ef608RmhIv.adMnfu5JS98Wyd0yNehW', TRUE);
