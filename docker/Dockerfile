FROM php:8.2-apache

# Global ServerName (elak warning)
RUN echo "ServerName esports.dev" >> /etc/apache2/apache2.conf

# Enable modules
RUN a2enmod rewrite ssl

# Disable default site
RUN a2dissite 000-default.conf

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable our site
COPY apache/esports.dev.conf /etc/apache2/sites-available/esports.dev.conf
RUN a2ensite esports.dev.conf

# SSL certs
COPY apache/ssl /etc/apache2/ssl

WORKDIR /var/www/html
