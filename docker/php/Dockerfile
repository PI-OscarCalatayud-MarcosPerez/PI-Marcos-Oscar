# Fem servir 'alpine' perquè és una versió molt més lleugera de PHP
FROM php:8.2-fpm-alpine

# 1. Instal·lem les dependències del sistema (llibreries)
#    - libxml2-dev (necessària per a l'extensió 'dom')
#    - libzip-dev (necessària per a l'extensió 'zip')
#    - libpng-dev, libjpeg-turbo-dev, freetype-dev (necessàries per a 'gd')
RUN apk add --no-cache \
    libxml2-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zlib-dev

# 2. Instal·lem les extensions de PHP
#    (Configurem 'gd' abans d'instal·lar-la)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        dom \
        zip \
        gd \
        pdo_mysql

# 3. Instal·lem Composer (una bona pràctica)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Definim el directori de treball (això ja ho teníeu)
WORKDIR /var/www/html