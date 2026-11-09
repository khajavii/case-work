FROM php:8.2-fpm

# ---------------------------------------------------------
# System dependencies
# ---------------------------------------------------------

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libmagickwand-dev \
    && rm -rf /var/lib/apt/lists/*


# ---------------------------------------------------------
# PHP extensions
# ---------------------------------------------------------

RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp

RUN docker-php-ext-install -j$(nproc) \
    bcmath \
    exif \
    gd \
    intl \
    mbstring \
    mysqli \
    pdo_mysql \
    pcntl \
    zip


# ---------------------------------------------------------
# Redis
# ---------------------------------------------------------

RUN pecl install redis \
    && docker-php-ext-enable redis


# ---------------------------------------------------------
# Composer
# ---------------------------------------------------------

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


# ---------------------------------------------------------
# PHP configuration
# ---------------------------------------------------------

WORKDIR /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini


# ---------------------------------------------------------
# PsySH writable directory (tinker)
# ---------------------------------------------------------

RUN mkdir -p /.config/psysh && chmod 777 /.config/psysh


# ---------------------------------------------------------
# PHP-FPM
# ---------------------------------------------------------

CMD ["php-fpm"]
