# SymSocial

Red social privada desarrollada con PHP y Symfony 7.2. Proyecto personal de [abrahampauta.com](https://abrahampauta.com).

Los usuarios se registran, activan su cuenta por correo, gestionan su perfil con foto, envían solicitudes de amistad y publican contenido que solo sus amigos pueden ver y comentar.

## Funcionalidades

- Landing page pública con prototipo visual del producto.
- Registro con activación de cuenta por correo electrónico (token de un solo uso).
- Login/logout con Symfony Security: CSRF, rate limiting y cuentas inactivas bloqueadas.
- Recuperación de contraseña por correo con token seguro y caducidad.
- Feed cronológico con las publicaciones propias y de amigos.
- Página "Descubrir" con buscador de usuarios.
- Perfiles con foto, edición de datos y privacidad: las publicaciones solo son visibles para amigos.
- Sistema de amistades: enviar, aceptar y rechazar solicitudes, con contador de pendientes en la barra de navegación.
- Publicaciones y comentarios (envío y borrado sin recargar la página).

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

3. Levantar la base de datos del proyecto (MySQL del docker-compose):

   ```bash
   docker compose up -d mysql
   ```

4. Crear el esquema con las migraciones y cargar los datos de demostración:

   ```bash
   php bin/console doctrine:migrations:migrate -n
   php bin/console doctrine:fixtures:load -n
   ```

5. Arrancar el servidor de desarrollo:

   ```bash
   symfony server:start -d
   ```

6. Abrir `http://127.0.0.1:8000/`. Los usuarios de demostración (`abraham@social.com`,
   `lucia@example.com`, `marco@example.com`, `sara@example.com`, `alex@example.com`)
   comparten la contraseña `symsocial123`.

## Despliegue con Docker (producción)

El stack completo (app + MySQL + phpMyAdmin interno) se levanta con:

```bash
docker compose up -d --build          # construir y arrancar todo
docker compose exec app php bin/console doctrine:migrations:migrate -n
```

| Servicio | URL local | Notas |
|---|---|---|
| Aplicación | `http://127.0.0.1:8090` | En el VPS, detrás de Caddy |
| phpMyAdmin | `http://127.0.0.1:8082` | Solo interno, nunca expuesto |
| MySQL | `127.0.0.1:3308` | Volumen `symsocial_mysql_data` |

Comandos habituales:

```bash
docker compose logs -f app     # logs de la aplicación
docker compose down            # parar (los datos persisten en los volúmenes)
docker compose down -v         # parar Y BORRAR los datos
```

## Arquitectura

```
src/
├── Controller/     # Controladores finos: validan la petición y delegan
├── Entity/         # Usuario, Publicacion, Comentario, Amistad (tipadas, con constraints)
├── Enum/           # EstadoAmistad (backed enum de PHP 8.1 persistido por Doctrine)
├── Repository/     # Consultas de dominio (feed, amistades, búsqueda de usuarios)
├── Security/       # UserChecker + Voters (permisos de perfil, publicación, comentario, amistad)
├── Service/        # EmailManager (correos transaccionales)
├── DataFixtures/   # Datos de demostración
└── Twig/           # Extensión con helpers de plantilla
migrations/         # Esquema de base de datos versionado (Doctrine Migrations)
templates/          # Vistas Twig (base + landing + app)
assets/styles/      # Sistema de diseño único (variables CSS, servido con AssetMapper)
tests/Functional/   # Tests funcionales (acceso, registro, privacidad, feed)
```

La autorización usa Voters de Symfony: la privacidad entre amigos se decide en
`PublicacionVoter`/`PerfilVoter`, no en los controladores.

## Calidad de código

```bash
# Tests funcionales (necesitan la base de datos de test)
php bin/console --env=test doctrine:database:create --if-not-exists
php bin/console --env=test doctrine:migrations:migrate -n
php bin/phpunit

# Análisis estático (PHPStan nivel 6, sin baseline)
vendor/bin/phpstan analyse
```

## Estado del proyecto

Seguridad reforzada (tokens de un solo uso, CSRF, rate limiting), interfaz rediseñada
con landing pública, y lógica organizada en capas. Pendiente:

- Dockerización y despliegue en VPS (Caddy + Docker Compose) bajo subdominio propio.
