FROM php:8.3-apache

# System deps for composer and PHP extensions
RUN apt-get update \
    && apt-get install -y git unzip \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Apache config: serve from /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/default-ssl.conf \
    && a2enmod rewrite

WORKDIR /var/www/html
COPY . /var/www/html

# Install PHP dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader

EXPOSE 80
CMD ["apache2-foreground"]
