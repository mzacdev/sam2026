FROM php:8.2-apache

# Enable Apache modules
RUN a2enmod rewrite

# (OPTIONAL) PHP extensions – tambah bila perlu
# RUN docker-php-ext-install mysqli pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html
