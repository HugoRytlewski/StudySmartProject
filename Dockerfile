FROM richarvey/nginx-php-fpm:2.1.2

# Installation des dépendances système nécessaires
RUN apk add --no-cache \
    git \
    zip \
    unzip

# Configuration du répertoire de travail
WORKDIR /var/www/html

# Copie des fichiers du projet
COPY . .

# Installation des dépendances Composer
RUN composer install --optimize-autoloader --no-dev

# Configuration des permissions
RUN chown -R nginx:nginx /var/www/html

# Variables d'environnement
ENV SKIP_COMPOSER 0
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1
ENV APP_ENV prod
ENV COMPOSER_ALLOW_SUPERUSER 1

# Commande de démarrage
CMD ["/start.sh"]
