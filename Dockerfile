FROM php:8.2-apache

# Instalar extensiones de PDO para MariaDB
RUN docker-php-ext-install pdo pdo_mysql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Configurar Apache DocumentRoot a public/
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# 1. Primero copiam TODO el código fuente del proyecto (incluyendo src/, public/, composer.json y composer.lock)
COPY . .

# 2. Ahora ejecuta composer install con todo el contexto disponible
RUN composer install --no-interaction --optimize-autoloader

CMD ["apache2-foreground"]