# Use the official PHP image with Apache
FROM php:8.2-apache

# Copy project files into the container
COPY . /var/www/html/

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Expose port 80 for the web server
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
