FROM php:8.2-apache

# Install base dependencies and SQLite support
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql

# Copy project files to the apache root
COPY . /var/www/html/

# Ensure permissions and writable database/logs folders
RUN mkdir -p /var/www/html/database /var/www/html/logs \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/database /var/www/html/logs

# Enable Apache mod_rewrite for clean URLs if needed (.htaccess)
RUN a2enmod rewrite

# Expose port (Render uses 80 by default)
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
