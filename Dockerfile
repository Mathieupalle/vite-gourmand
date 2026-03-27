# --- Image de base ---
FROM php:8.2-apache

# --- Dépendances système et client MySQL ---
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    libssl-dev \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# --- Extensions PHP MySQL ---
RUN docker-php-ext-install pdo pdo_mysql mysqli

# --- Extension MongoDB (pour Atlas) ---
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# --- Configuration Apache ---
COPY apache.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# --- Définir le répertoire de travail ---
WORKDIR /var/www/html

# --- Copier le projet dans le conteneur ---
COPY . /var/www/html

# --- Installer Composer depuis l'image officielle ---
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

