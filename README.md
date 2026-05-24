# Galería PHP con usuarios y administrador

Aplicación web responsive para gestionar imágenes con PHP, SQLite, HTML, CSS y JavaScript.

## Funciones

- Registro e inicio de sesión.
- Rol `user`: ve, sube y elimina solo sus imágenes.
- Rol `admin`: edita usuarios, cambia roles, elimina usuarios e imágenes.
- Subida segura con validación de tipo MIME y tamaño máximo de 5 MB.
- Protección CSRF y contraseñas con `password_hash`.
- Base de datos SQLite generada automáticamente.

## Requisitos

- PHP 8 o superior.
- Extensiones PHP: `pdo_sqlite`, `fileinfo` o soporte `getimagesize`.

## Ejecución local

```bash
cd galeria_php
php -S localhost:8000
```

Abre `http://localhost:8000` en tu navegador.

## Usuarios demo

- Administrador: `admin@demo.com` / `Admin123!`
- Usuario: `user@demo.com` / `User123!`

## Estructura

```text
config.php          Configuración, conexión, seguridad y helpers
schema.sql          Tablas users/images
index.php           Galería privada del usuario
admin.php           Panel de administración
login.php           Inicio de sesión
register.php        Registro
logout.php          Cierre de sesión
assets/             CSS y JavaScript
uploads/            Archivos subidos por usuario
data/               Base SQLite
```
