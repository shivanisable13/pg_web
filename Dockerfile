# ============================================================
# CampusStay Production Dockerfile
# PHP 8.2 + Apache
# ============================================================

FROM php:8.2-apache

# ============================================================
# INSTALL SYSTEM PACKAGES + PHP EXTENSIONS
# ============================================================

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    git \
    mariadb-client \
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

# ============================================================
# ENABLE APACHE MODULES
# ============================================================

RUN a2enmod rewrite headers

# ============================================================
# SECURITY SETTINGS
# ============================================================

RUN echo "ServerTokens Prod" >> /etc/apache2/apache2.conf \
    && echo "ServerSignature Off" >> /etc/apache2/apache2.conf

# ============================================================
# SET APACHE DOCUMENT ROOT
# ============================================================

ENV APACHE_DOCUMENT_ROOT=/var/www/html

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf

RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# ============================================================
# ENABLE .HTACCESS
# ============================================================

RUN sed -i 's/AllowOverride None/AllowOverride All/g' \
    /etc/apache2/apache2.conf

# ============================================================
# WORKING DIRECTORY
# ============================================================

WORKDIR /var/www/html

# Remove default Apache page
RUN rm -f /var/www/html/index.html

# ============================================================
# COPY APPLICATION FILES
# ============================================================

COPY . /var/www/html/

# ============================================================
# CREATE DEFAULT .HTACCESS IF MISSING
# ============================================================

RUN if [ ! -f /var/www/html/.htaccess ]; then \
    echo "Options -Indexes\n\
RewriteEngine On\n\
RewriteCond %{REQUEST_FILENAME} !-f\n\
RewriteCond %{REQUEST_FILENAME} !-d\n\
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]" \
> /var/www/html/.htaccess; \
fi

# ============================================================
# CREATE UPLOAD DIRECTORIES
# ============================================================

RUN mkdir -p \
    /var/www/html/uploads \
    /var/www/html/uploads/pgs \
    /var/www/html/uploads/profiles \
    /var/www/html/uploads/temp

# ============================================================
# FILE PERMISSIONS
# ============================================================

RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && chmod -R 775 /var/www/html/uploads

# ============================================================
# PHP CONFIGURATION
# ============================================================

RUN echo "upload_max_filesize = 20M" > /usr/local/etc/php/conf.d/campusstay.ini \
    && echo "post_max_size = 25M" >> /usr/local/etc/php/conf.d/campusstay.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/campusstay.ini \
    && echo "max_execution_time = 120" >> /usr/local/etc/php/conf.d/campusstay.ini \
    && echo "max_input_time = 120" >> /usr/local/etc/php/conf.d/campusstay.ini \
    && echo "date.timezone = Asia/Kolkata" >> /usr/local/etc/php/conf.d/campusstay.ini \
    && echo "display_errors = Off" >> /usr/local/etc/php/conf.d/campusstay.ini \
    && echo "log_errors = On" >> /usr/local/etc/php/conf.d/campusstay.ini

# ============================================================
# HEALTH CHECK
# ============================================================

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s \
CMD curl -f http://localhost/ || exit 1

# ============================================================
# EXPOSE PORT
# ============================================================

EXPOSE 80

# ============================================================
# START APACHE
# ============================================================

CMD ["apache2-foreground"]
