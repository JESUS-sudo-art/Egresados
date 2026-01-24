# Reporte Final - Proyecto Egresados

## Herramientas Tecnológicas Utilizadas

### Resumen Ejecutivo

- **Backend**: Laravel 12 (PHP 8.2/8.3), Inertia Laravel, Fortify, permisos de Spatie.
- **Frontend**: Vue 3 + TypeScript, Vite 7, Tailwind CSS 4, shadcn-vue, Radix Vue, Chart.js.
- **Infraestructura**: Docker (Nginx, PHP-FPM, MySQL 8), Traefik, WSL + NVM.
- **Dev tooling**: ESLint 9 (flat), Prettier 3, Laravel Pint, PHPUnit 11, Mockery, Concurrently.

---

## Stack Tecnológico Detallado

### Backend

#### Lenguaje y Framework
- **PHP**: ^8.2 requerido en `composer.json`, contenedor PHP 8.3 en `docker/php/Dockerfile`.
- **Framework**: `laravel/framework` ^12.0
- **Servidor**: PHP-FPM 8.3 en Docker

#### Paquetes Principales
- **Autenticación**: `laravel/fortify` - Sistema de autenticación moderno
- **Puente Frontend-Backend**: `inertiajs/inertia-laravel` ^2.0 - Soporte SSR
- **Roles y Permisos**: `spatie/laravel-permission` ^6.23 - Gestión de permisos y roles
- **Generación de PDFs**: `barryvdh/laravel-dompdf` ^3.1
- **Códigos QR**: `endroid/qr-code` ^6.0
- **Database**: `spatie/db-dumper` ^3.8
- **Dev Console**: `laravel/tinker` ^2.10.1
- **Rutas/Navegación**: `laravel/wayfinder` ^0.1.9

#### Testing y Desarrollo
- **Testing**: `phpunit/phpunit` ^11.5.3
- **Mocking**: `mockery/mockery` ^1.6
- **Debugging**: `nunomaduro/collision` ^8.6
- **Datos de Prueba**: `fakerphp/faker` ^1.23
- **Linting PHP**: `laravel/pint` ^1.18
- **Logs en Tiempo Real**: `laravel/pail` ^1.2.2

---

### Frontend

#### Framework y Build Tools
- **Runtime**: Vue 3 ^3.5.13
- **Lenguaje**: TypeScript ^5.2.2
- **Bundler**: Vite ^7.0.4
- **Plugin Larvel**: `laravel-vite-plugin` ^2.0.0 y `@laravel/vite-plugin-wayfinder` ^0.1.3
- **Soporte SSR**: Scripts `build:ssr` y `dev:ssr` con Inertia

#### Estilos y Utilidades
- **CSS Framework**: Tailwind CSS ^4.1.1 con `@tailwindcss/vite` ^4.1.11
- **Configuración**: `tailwind.config.js` definido en `components.json` (shadcn-vue)
- **CSS Variable**: Habilitadas en config
- **Post-procesamiento**: Merge de clases (`tailwind-merge`), utilidades (`clsx`)

#### Componentes y UI
- **Componentes Base**: shadcn-vue (vía schema en `components.json`)
- **Biblioteca de UI**: `radix-vue` ^1.9.9 para primitivas accesibles
- **Componentes de Negocio**: `reka-ui` ^2.4.1
- **Iconos**: `lucide-vue-next` ^0.468.0 (esquema de `components.json`)
- **Utilidades**: `class-variance-authority` ^0.7.1 para variantes de clases

#### Datos y Gráficas
- **Gráficas**: `chart.js` ^4.4.4 + `vue-chartjs` ^5.3.1
- **Estado/Composables**: `@vueuse/core` ^12.8.2

#### TypeScript y Validación
- **Verificación**: `vue-tsc` ^2.2.4
- **Type Hints para Node**: `@types/node` ^22.13.5

#### Linting y Formateo
- **Linter**: ESLint ^9.17.0 (ESM flat config en `eslint.config.js`)
  - Plugins: `eslint-plugin-vue` ^9.32.0, `@vue/eslint-config-typescript` ^14.3.0
  - Integración: `eslint-config-prettier` ^10.0.1
- **Formateador**: Prettier ^3.4.2
  - Plugins: `prettier-plugin-organize-imports` ^4.1.0, `prettier-plugin-tailwindcss` ^0.6.11

#### Dependencias Opcionales (Platform-specific)
- Compiladores optimizados para Linux y Windows:
  - `@rollup/rollup-linux-x64-gnu`, `@rollup/rollup-win32-x64-msvc`
  - `@tailwindcss/oxide-*`, `lightningcss-*`

---

### Infraestructura

#### Orquestación (Docker Compose)
Archivo: `docker-compose.yml`

**Servicios:**

1. **Nginx** (nginx:alpine)
   - Container: `egresados-nginx`
   - Volúmenes: Proyecto como `/var/www/html`, configuración en `docker/nginx/default.conf`
   - Redes: `mi-red-egresados` (interna) + `web-proxy` (externa para Traefik)
   - Puertos: 80 (interno para Traefik)

2. **PHP-FPM** (php:8.3-fpm custom)
   - Container: `egresados-php`
   - Dockerfile: `docker/php/Dockerfile`
   - Usuario: `www-data` (sincronizado con UID/GID local)
   - Extensiones PHP: `pdo_mysql`, `mbstring`, `exif`, `pcntl`, `bcmath`, `gd`
   - Volúmenes: Código fuente + SSH keys (`~/.ssh:ro`)
   - Redes: `mi-red-egresados`

3. **MySQL** (mysql:8.0)
   - Container: `egresados-db`
   - BD: `egresados_db`
   - Credenciales: user/password, root password: root
   - Autenticación: `mysql_native_password`
   - Volumen: `db_data` (persistencia)
   - Puerto Host: 3307 → Container 3306

#### Proxy Inverso
- **Traefik** (red externa `web-proxy`)
- Ruta: `egresados.test` 
- Entry Points: `web` (HTTP)

#### Redes
- `mi-red-egresados`: Red privada entre Nginx, PHP, MySQL
- `web-proxy`: Red externa para Traefik (debe ser creada externamente)

#### Volúmenes
- `db_data`: Persistencia de datos MySQL

---

### Entorno de Desarrollo

#### Sistema Base
- **OS**: Ubuntu (WSL - Windows Subsystem for Linux)
- **Node Version Manager**: NVM configurado en `~/.nvm`
- **Node**: Versión default vía NVM

#### Ejecución Orquestada
Tarea VS Code: `Dev (WSL sanitized)`

Script que ejecuta:
```bash
wsl -d Ubuntu -e bash -lc 'export NVM_DIR="$HOME/.nvm"; . "$NVM_DIR/nvm.sh"; nvm use default >/dev/null; cd /home/jorte/proyectos/Egresados; npm run dev'
```

Ejecuta concurrentemente (vía `composer run dev`):
- `php artisan serve` - Servidor Laravel (puerto 8000)
- `php artisan queue:listen --tries=1` - Cola de trabajos
- `php artisan pail --timeout=0` - Logs en tiempo real
- `npm run dev` - Vite dev server

#### Alternativa SSR
```bash
composer run dev:ssr
```
Adiciona: `php artisan inertia:start-ssr` en lugar de `npm run dev`

---

## Scripts NPM

| Comando | Función |
|---------|---------|
| `npm run dev` | Inicia Vite dev server (HMR habilitado) |
| `npm run build` | Build de producción frontend |
| `npm run build:ssr` | Build SSR + cliente |
| `npm run format` | Formatea código en `resources/` con Prettier |
| `npm run format:check` | Verifica formato sin cambios |
| `npm run lint` | Ejecuta ESLint con auto-fix |

---

## Scripts Composer

| Comando | Función |
|---------|---------|
| `composer run setup` | Instalación inicial (Composer, .env, migraciones, npm) |
| `composer run dev` | Orquesta servidor, cola, logs, Vite con concurrently |
| `composer run dev:ssr` | Versión SSR con Inertia |
| `composer run test` | Ejecuta PHPUnit (limpia config primero) |

---

## Configuración Notable

### TypeScript
- `tsconfig.json` (implícito): Soporte completo para Vue 3 + TypeScript

### Tailwind CSS
- Configuración en `tailwind.config.js` (referenciado en `components.json`)
- Integración shadcn-vue con variables CSS
- Base color: `neutral`
- Plugins: `@tailwindcss/vite` para compilación rápida

### Componentes UI
- Configurados en `components.json` (shadcn-vue schema)
- Path aliases:
  - `@/components` → `resources/js/components`
  - `@/ui` → `resources/js/components/ui`
  - `@/lib` → `resources/js/lib`
  - `@/composables` → `resources/js/composables`

### Dockerfile PHP
Personalización en `docker/php/Dockerfile`:
- Argumentos UID/GID para sincronización de permisos
- Instalación de Composer (`composer:latest`)
- Configuración custom en `docker/php/conf.d/user.ini`
- Permiso de archivos a `www-data`

---

## Resumen de Versiones Clave

| Herramienta | Versión | Rol |
|-------------|---------|-----|
| PHP | 8.3 | Runtime backend |
| Laravel | 12.0 | Framework backend |
| Vue | 3.5.13 | Runtime frontend |
| Vite | 7.0.4 | Bundler |
| TypeScript | 5.2.2 | Tipado estático JS |
| Tailwind CSS | 4.1.1 | Estilos utility-first |
| MySQL | 8.0 | Base de datos |
| Nginx | Alpine | Servidor web |
| ESLint | 9.17.0 | Linter JS/TS |
| Prettier | 3.4.2 | Formateador código |
| PHPUnit | 11.5.3 | Testing PHP |

---

## Documentación Relacionada

- `composer.json` - Dependencias PHP, scripts
- `package.json` - Dependencias Node, scripts npm
- `docker-compose.yml` - Orquestación de servicios
- `docker/php/Dockerfile` - Imagen PHP personalizada
- `eslint.config.js` - Configuración de linting
- `components.json` - Configuración shadcn-vue y Tailwind

---

**Última actualización**: 23 de enero de 2026
