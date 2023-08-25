FROM php:8.2-apache

# Installer les extensions PDO et mysqli
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Activer le module rewrite d'Apache (optionnel)
RUN a2enmod rewrite

# Copier les fichiers source dans le container
COPY ./html /var/www/html