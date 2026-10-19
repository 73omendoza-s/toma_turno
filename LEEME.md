# Sistema de control y seguimiento de turnos (Laravel 11/12)

## Instalación (terminal de Visual Studio Code)

```bash
composer create-project laravel/laravel turnos
cd turnos
```

1. Copia el contenido de esta carpeta **encima** del proyecto (acepta reemplazar
   `routes/web.php` y `database/migrations/0001_01_01_000000_create_users_table.php`).
2. En `.env` ajusta la zona horaria (afecta la numeración diaria de turnos):

```
APP_TIMEZONE=America/Mazatlan
APP_LOCALE=es
```

3. Crear tablas, datos iniciales y arrancar:

```bash
php artisan migrate:fresh --seed
php artisan serve
php artisan test
```

Abre http://127.0.0.1:8000 · Pantalla de sala: http://127.0.0.1:8000/pantalla

No necesita Node ni `npm run dev`.

## Usuarios de prueba (cámbialos)

| Usuario | Contraseña | Rol |
|---|---|---|
| admin | (ver SEED_ADMIN_PASSWORD en .env) | Administrador |
| recepcion | (ver SEED_RECEPCION_PASSWORD en .env) | Recepción |
| caja1 | (ver SEED_CAJA1_PASSWORD en .env) | Operador de Caja 1 |

## PostgreSQL en vez de SQLite

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=turnos
DB_USERNAME=postgres
DB_PASSWORD=...
```
