# Use the official PHP-Apache image
FROM php:8.2-apache

# Copy project files to the web root
COPY . /var/www/html/

# Enable PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Make sure uploads folder is accessible
RUN mkdir -p /var/www/html/uploads && chmod -R 755 /var/www/html/uploads

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]

# Expose uploads folder to web
RUN mkdir -p /var/www/html/uploads
COPY uploads /var/www/html/uploads
