# Red Social

Red social desarrollada con PHP y Symfony 7.2. Proyecto personal de [abrahampauta.com](https://abrahampauta.com).

Los usuarios se registran, activan su cuenta por correo, gestionan su perfil con foto, envían solicitudes de amistad y publican contenido que solo sus amigos pueden ver y comentar.

## Funcionalidades

- Registro con activación de cuenta por correo electrónico.
- Login/logout con Symfony Security (contraseñas hasheadas, cuentas inactivas bloqueadas).
- Recuperación de contraseña por correo.
- Perfiles con foto, edición de datos y privacidad: las publicaciones solo son visibles para amigos.
- Sistema de amistades: enviar, aceptar y rechazar solicitudes, con contador de pendientes.
- Publicaciones y comentarios.

## Stack

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.2+, Symfony 7.2 |
| ORM | Doctrine ORM 3 |
| Base de datos | MySQL 8 |
| Frontend | Twig, Stimulus, Turbo, AssetMapper |
| Correo | Symfony Mailer (SMTP) |

## Requisitos

- PHP >= 8.2 con extensiones `ctype` e `iconv`
- Composer
- MySQL 8 (nativo o en Docker)
- Symfony CLI (opcional, recomendado para el servidor de desarrollo)

## Puesta en marcha en local

1. Clonar el repositorio e instalar dependencias:

   ```bash
   composer install
   ```

2. Crear la configuración local:

   ```bash
   cp .env.example .env
   ```

   Rellenar `APP_SECRET`, `DATABASE_URL` y `MAILER_DSN`. El archivo `.env` no se versiona.

3. Levantar MySQL. Ejemplo con Docker:

   ```bash
   docker run -d --name redsocial-mysql \
     -e MYSQL_ALLOW_EMPTY_PASSWORD=yes \
     -e MYSQL_DATABASE=redsocial \
     -p 3306:3306 \
     -v redsocial_mysql_data:/var/lib/mysql \
     mysql:8.0
   ```

4. Importar el esquema y los datos de ejemplo:

   ```bash
   docker exec -i redsocial-mysql mysql -uroot < redsocial.sql
   ```

5. Arrancar el servidor de desarrollo:

   ```bash
   symfony server:start -d
   ```

6. Abrir `http://127.0.0.1:8000/login`. El esquema incluye usuarios de ejemplo.

## Estructura

```
src/
├── Controller/   # Login, registro, perfil, publicaciones, amistades, comentarios
├── Entity/       # Usuario, Publicacion, Comentario, Amistad
└── Security/     # UserChecker (bloquea cuentas sin activar)
templates/        # Vistas Twig
public/css/       # Hojas de estilo por vista
redsocial.sql     # Esquema de base de datos y datos de ejemplo
```

## Estado del proyecto

En evolución hacia despliegue en producción bajo subdominio propio. Pendientes:

- Refuerzo de seguridad (tokens de activación y recuperación, CSRF, validación de entrada).
- Rediseño completo de la interfaz y landing page pública.
- Dockerización y despliegue en VPS (Caddy + Docker Compose).
