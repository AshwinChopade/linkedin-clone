# Use the official PHP-Apache image
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Copy project files to the web root
COPY . /var/www/html/

# Enable PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Ensure uploads folder exists and set correct permissions
RUN mkdir -p /var/www/html/uploads \
    && chmod -R 777 /var/www/html/uploads

# Expose port 80 for web traffic
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]
