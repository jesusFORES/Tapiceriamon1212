FROM php:8.1-apache

RUN apt-get update \
  && apt-get install -y --no-install-recommends libonig-dev libzip-dev unzip git \
  && docker-php-ext-install pdo_mysql \
  && a2enmod rewrite \
  && rm -rf /var/lib/apt/lists/*

# Copy virtual host config
COPY config/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Expose port 80
EXPOSE 80

# Copy entrypoint script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
