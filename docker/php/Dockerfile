# Usamos la imagen oficial de PHP 8.3 con FPM como base
FROM php:8.3-fpm

# Argumentos para pasar los IDs de usuario y grupo durante la construcción.
# Por defecto son 1000, que coincide con tu caso.
ARG UID=1000
ARG GID=1000


# Modificamos el grupo 'www-data' existente para que tenga el GID de tu usuario
RUN groupmod -g $GID www-data
# Modificamos el usuario 'www-data' existente para que tenga el UID y GID de tu usuario
RUN usermod -u $UID -g $GID www-data


# Establecemos el directorio de trabajo dentro del contenedor
WORKDIR /var/www/html

# Instalamos dependencias del sistema operativo que son necesarias para las extensiones de PHP
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Limpiamos la caché para mantener la imagen final ligera
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Usamos el script oficial para instalar las extensiones de PHP que Laravel necesita
# pdo_mysql es para la conexión a MySQL (más moderno que mysqli)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Instalamos Composer (el gestor de dependencias de PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY docker/php/conf.d/user.ini /usr/local/etc/php/conf.d/user.ini

# Cambiamos el propietario de los archivos a www-data para evitar problemas de permisos
RUN chown -R www-data:www-data /var/www/html
