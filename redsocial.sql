SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO"; 
SET time_zone = "+00:00";

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS redsocial;
USE redsocial;

-- Estructura de tabla para la tabla `usuarios`
CREATE TABLE `usuarios` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre_usuario` VARCHAR(255) NOT NULL,
  `contrasena` VARCHAR(255) NOT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre_usuario` (`nombre_usuario`) 
);

-- Estructura de tabla para la tabla `publicaciones`
CREATE TABLE `publicaciones` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `contenido` VARCHAR(255) NOT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`)
);

-- Estructura de tabla para la tabla `reacciones`
CREATE TABLE `reacciones` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_publicacion` INT NOT NULL,
  `id_usuario` INT NOT NULL,
  `tipo` VARCHAR(255) NOT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_publicacion` (`id_publicacion`),
  KEY `id_usuario` (`id_usuario`)
);

-- Relaciones entre las tablas
ALTER TABLE `publicaciones`
  ADD CONSTRAINT `fk_publicaciones_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

ALTER TABLE `reacciones`
  ADD CONSTRAINT `fk_reacciones_publicaciones` FOREIGN KEY (`id_publicacion`) REFERENCES `publicaciones` (`id`),
  ADD CONSTRAINT `fk_reacciones_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

USE redsocial;

-- Insertar usuarios con contraseñas hasheadas en la tabla `usuarios`
INSERT INTO `usuarios` (`nombre_usuario`, `contrasena`, `fecha_creacion`)
VALUES
('abraham@social.com', PASSWORD('1234'), CURRENT_TIMESTAMP),
('alejo@social.com', PASSWORD('1234'), CURRENT_TIMESTAMP),
('alex@social.com', PASSWORD('1234'), CURRENT_TIMESTAMP);