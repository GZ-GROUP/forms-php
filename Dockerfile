FROM php:8.2-apache

# Dependencias del sistema + extensiones PHP
RUN apt-get update && apt-get install -y \
        libpq-dev \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        session \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite
RUN a2enmod rewrite

# PHP ini para producción segura
RUN echo "session.cookie_httponly = 1"    >> /usr/local/etc/php/php.ini && \
    echo "session.cookie_samesite = Strict" >> /usr/local/etc/php/php.ini && \
    echo "session.use_strict_mode = 1"    >> /usr/local/etc/php/php.ini && \
    echo "expose_php = Off"               >> /usr/local/etc/php/php.ini && \
    echo "display_errors = Off"           >> /usr/local/etc/php/php.ini && \
    echo "log_errors = On"               >> /usr/local/etc/php/php.ini

# Copiar código fuente
COPY src/ /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

EXPOSE 80