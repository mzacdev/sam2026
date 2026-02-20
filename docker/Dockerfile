FROM php:8.2-apache

# Global ServerName (elak warning)
RUN echo "ServerName esports.dev" >> /etc/apache2/apache2.conf

# Enable modules
RUN a2enmod rewrite ssl

# Disable default site
RUN a2dissite 000-default.conf

# System deps for Sybase + certificate PDF generation
RUN apt-get update && apt-get install -y --no-install-recommends \
    unixodbc-dev \
    freetds-dev \
    freetds-bin \
    tdsodbc \
    chromium \
    fonts-dejavu-core \
    fonts-dejavu-extra \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure pdo_odbc --with-pdo-odbc=unixODBC,/usr \
    && docker-php-ext-install -j"$(nproc)" \
       pdo \
       pdo_mysql \
       pdo_odbc \
       pdo_dblib

# Enable our site
COPY apache/esports.dev.conf /etc/apache2/sites-available/esports.dev.conf
RUN a2ensite esports.dev.conf

# SSL certs
COPY apache/ssl /etc/apache2/ssl

WORKDIR /var/www/html
