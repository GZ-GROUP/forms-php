FROM php:8.2-apache

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Activar mod_rewrite (opcional)
RUN a2enmod rewrite

# Copiar proyecto
COPY . /var/www/html/

# Permisos (importante en producción)
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80