FROM richarvey/nginx-php-fpm:2.1.2

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application dans le conteneur
COPY . .

# Installer les dépendances PHP nécessaires (par exemple GD si tu l'utilises)
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd

# Variables d'environnement
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1
ENV APP_ENV prod
ENV COMPOSER_ALLOW_SUPERUSER 1

# Afficher un message pour vérifier si composer est installé avant d'exécuter la commande
RUN echo "Running composer install..." && composer install --no-interaction --optimize-autoloader

# Commande à exécuter au démarrage
CMD ["/start.sh"]
