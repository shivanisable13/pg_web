# ============================================================
# Dockerfile for CampusStay - PHP PG Booking Platform
# Stack: PHP 8.2 + Apache + MySQL (via docker-compose)
# ============================================================

FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    git \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mysqli \
        gd \
        zip \
        exif \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers

# Set Apache document root
ENV APACHE_DOCUMENT_ROOT /var/www/html

# Configure Apache virtual host
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Allow .htaccess overrides for URL rewriting
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Copy application source code
COPY . /var/www/html/

# Create .htaccess if not present
RUN if [ ! -f /var/www/html/.htaccess ]; then \
    echo "Options -Indexes\nRewriteEngine On\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^(.*)$ index.php?url=$1 [QSA,L]" > /var/www/html/.htaccess; \
fi

# Create uploads directory and set proper permissions
RUN mkdir -p /var/www/html/uploads/pgs \
             /var/www/html/uploads/profiles \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads

# PHP configuration for production-ready settings
RUN echo "upload_max_filesize = 10M" >> /usr/local/etc/php/conf.d/campusstay.ini \
    && echo "post_max_size = 12M" >> /usr/local/etc/php/conf.d/campusstay.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/campusstay.ini \
    && echo "max_execution_time = 120" >> /usr/local/etc/php/conf.d/campusstay.ini \
    && echo "date.timezone = Asia/Kolkata" >> /usr/local/etc/php/conf.d/campusstay.ini

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
