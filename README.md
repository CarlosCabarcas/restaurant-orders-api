# 🍽️ Restaurant Orders - Aplicación Laravel con Docker

Una aplicación Laravel para gestión de pedidos de restaurante, containerizada con Docker, PostgreSQL y Redis.

## 📋 Requisitos Previos

Antes de comenzar, asegúrate de tener instalado:

- **Docker Desktop** (v20.10 o superior)
- **Docker Compose** (v2.0 o superior)
- **Git**

### Verificar instalación:
```bash
docker --version
docker-compose --version
git --version
```

## 🚀 Instalación desde Cero

### 1. Clonar el Proyecto

#### Si es un proyecto existente:
```bash
git clone https://github.com/CarlosCabarcas/restaurant-orders-api
cd restaurant-orders-api/api
```

#### 2 Crear configuración de Nginx `~/docker/nginx/conf.d/default.conf`:
```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

#### 3 Configurar archivo `.env`:
```env
APP_NAME="Restaurant Orders"
APP_ENV=local
APP_KEY=base64:GENERA_TU_KEY_AQUI
APP_DEBUG=true
APP_URL=http://localhost:8080

LOG_CHANNEL=stack

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
QUEUE_CONNECTION=sync
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

### 4. Construir y Ejecutar la Aplicación

```bash
# 1. Construir las imágenes Docker
docker-compose build

# 2. Levantar todos los servicios (PostgreSQL, Redis, App, Nginx)
docker-compose up -d

# 3. Verificar que todos los contenedores estén ejecutándose
docker-compose ps
```

### 4. Configuración Inicial de Laravel

```bash
# Generar clave de aplicación
docker-compose exec app php artisan key:generate

# Limpiar cachés
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
```

**¡Listo!** 🎉 Tu aplicación está disponible en: **http://localhost:8080**


