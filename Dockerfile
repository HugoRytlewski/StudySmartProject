# Étape 1 : Construction de l'application Symfony
FROM php:8.2-fpm

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définition du répertoire de travail
WORKDIR /var/www

# Copie des fichiers du projet
COPY . .

# Installation des dépendances Symfony
RUN composer install --no-dev --optimize-autoloader

# Correction des permissions
RUN chown -R www-data:www-data /var/www

# Commande par défaut
CMD ["php-fpm"]
