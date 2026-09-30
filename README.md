# Sync — Tienda de relojes

Aplicación web académica desarrollada con Laravel 12 y MySQL. Sync ofrece un catálogo público de relojes, autenticación, lista de deseos, carrito con control de inventario, generación de pedidos y un panel administrativo separado para gestionar relojes y usuarios.

## Integrantes

- Isabela Ruiz de la Ossa
- Nicolás Ortiz Álvarez
- Miguel Ángel Rendón Quintero

## Requisitos

- PHP 8.2 o superior, con las extensiones requeridas por Laravel y MySQL.
- Composer 2.
- Node.js 20 o superior y npm.
- MySQL 8 o compatible.

## Instalación local

```bash
git clone https://github.com/SailBliss/Entrega-1_ARQSW.git
cd Entrega-1_ARQSW
composer install
npm install
cp .env.example .env
php artisan key:generate
```

En Windows PowerShell, el archivo de entorno se puede crear con:

```powershell
Copy-Item .env.example .env
```

Cree una base de datos MySQL llamada `tienda_relojes` y configure en `.env` las variables `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`. Después ejecute:

```bash
php artisan migrate --seed
npm run build
php artisan serve
```

La ruta principal es `http://127.0.0.1:8000/`. El panel administrativo se encuentra en `http://127.0.0.1:8000/admin`.

## Usuarios de demostración

Los seeders crean estas cuentas locales:

| Rol | Correo | Contraseña |
| --- | --- | --- |
| Administrador | `admin@example.com` | `password` |
| Usuario | `demo@example.com` | `password` |

Estas credenciales son exclusivamente para desarrollo y deben reemplazarse en un despliegue real.

## Funcionalidades

### Usuario final

- Catálogo, detalle y búsqueda por nombre, marca o descripción.
- Registro, inicio y cierre de sesión.
- Lista de deseos personal.
- Carrito por usuario con cantidades y validación de existencias.
- Compra transaccional: crea el pedido, descuenta inventario y vacía el carrito.
- Interfaz independiente del panel administrativo.

### Administración

- Acceso restringido por middleware y rol de administrador.
- CRUD completo de relojes.
- CRUD completo de usuarios.
- Protección contra eliminación de relojes asociados a pedidos.
- Protección contra eliminación de la cuenta administrativa en uso.

## Base de datos

El proyecto administra el esquema mediante migraciones y contiene modelos para usuarios, relojes, elementos del carrito, elementos de la lista de deseos, pedidos y elementos de pedido. Las relaciones del dominio están implementadas en ambos extremos con Eloquent. `DatabaseSeeder` crea usuarios de demostración y doce relojes ficticios.

## Calidad y pruebas

Las pruebas se ejecutan con una base SQLite en memoria para mantenerlas aisladas. La aplicación continúa configurada para MySQL.

```bash
php artisan test
vendor/bin/pint --test
npm run build
```

En Windows, Laravel Pint también puede ejecutarse con `vendor\bin\pint --test`.

## Estructura principal

```text
app/
├── Http/Controllers/       # Controladores públicos y administrativos
├── Http/Middleware/        # Autorización administrativa
└── Models/                 # Entidades y relaciones Eloquent
database/
├── factories/
├── migrations/
└── seeders/
resources/
├── lang/es/                # Textos visibles de la aplicación
└── views/
    ├── admin/              # Interfaz administrativa independiente
    ├── auth/
    ├── cart/
    ├── home/
    ├── watch/
    └── wishlist/
routes/web.php
tests/Feature/
```

## Documentación del proyecto

La identidad del equipo, el modelo verbal, los diagramas de clases y arquitectura MVC, la guía de estilo, las reglas de programación y las funcionalidades interesantes se encuentran en la [Wiki del repositorio](https://github.com/SailBliss/Entrega-1_ARQSW/wiki).

El trabajo del equipo se organiza en el [proyecto Backlog](https://github.com/users/SailBliss/projects/3) y en los issues del repositorio.
