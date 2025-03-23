# Utilisation de PHP 8.2 avec Apache
FROM php:8.2-apache

# Installer les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libicu-dev \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql intl

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application
COPY . .

# Créer un utilisateur "symfony" pour éviter les problèmes liés à root
RUN useradd -m symfony && chown -R symfony /var/www/html

# Passer à l'utilisateur "symfony" pour exécuter Composer sans erreurs
USER symfony
RUN composer install --no-dev --optimize-autoloader --no-scripts && composer clear-cache

# Revenir à l'utilisateur root
USER root

# Donner les bons droits aux fichiers de cache, logs et autres répertoires
RUN mkdir -p var tmp && chown -R www-data:www-data var tmp

# Activer mod_rewrite pour Symfony
RUN a2enmod rewrite

# Modifier la racine d'Apache pour Symfony
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Configurer Apache pour autoriser les réécritures sans .htaccess
RUN echo '<Directory /var/www/html/public>\n  Options Indexes FollowSymLinks\n  AllowOverride None\n  Require all granted\n  RewriteEngine On\n  RewriteRule ^(.*)$ index.php [QSA,L]\n</Directory>' >> /etc/apache2/sites-available/000-default.conf

# Exposer le port 80
EXPOSE 80

CMD ["apache2-foreground"]
