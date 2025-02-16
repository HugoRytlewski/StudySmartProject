FROM richarvey/nginx-php-fpm:2.1.2

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application dans le conteneur
COPY . .

# Mettre à jour PHP vers la version 8.2
RUN apk update && apk add --no-cache \
    php8.2 \
    php8.2-fpm \
    php8.2-cli \
    php8.2-json \
    php8.2-opcache \
    php8.2-pdo \
    php8.2-mbstring \
    php8.2-curl \
    php8.2-gd \
    php8.2-xml \
    php8.2-zip \
    php8.2-mysqli \
    php8.2-json \
    && php -v

# Installer les dépendances PHP nécessaires (par exemple GD si tu l'utilises)
RUN apk add --no-cache libpng-dev libjpeg-turbo-dev freetype-dev && \
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

# Exécuter composer install
RUN echo "Running composer install..." && composer install --no-interaction --optimize-autoloader

# Commande à exécuter au démarrage
CMD ["/start.sh"]
