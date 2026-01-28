# Use the official PHP image with Apache
FROM php:8.2-apache

# Install dependencies for PostgreSQL and MySQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy application files to the container
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/

# Expose port 80 (Fly.io maps this automatically)
EXPOSE 80
