# Use a base PHP image with Apache/FPM
FROM php:8.2-apache

# Install mysqli extension for MySQL database access
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache's rewrite module (useful for routing/clean URLs)
RUN a2enmod rewrite

# The web root is already set to /var/www/html by the base image
# Permissions for the web root
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 (default for Apache)
EXPOSE 80
