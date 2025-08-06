FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and only required PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    nodejs \
    npm \
    libzip-dev \
    && docker-php-ext-install pdo_mysql zip \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Accept build arguments for user configuration
ARG USER_ID=1000
ARG GROUP_ID=1000
ARG USER_NAME=appuser

# Create user with same UID/GID as host user
RUN groupadd -g ${GROUP_ID} ${USER_NAME} \
    && useradd -u ${USER_ID} -g ${GROUP_ID} -m -s /bin/bash ${USER_NAME} \
    && usermod -aG www-data ${USER_NAME}

# Configure Apache document root to Laravel's public directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Change Apache to run as our user
RUN sed -i "s/User www-data/User ${USER_NAME}/" /etc/apache2/apache2.conf
RUN sed -i "s/Group www-data/Group ${USER_NAME}/" /etc/apache2/apache2.conf

# Copy application files
COPY --chown=${USER_ID}:${GROUP_ID} . /var/www/html

# Set proper permissions for Laravel directories
RUN mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/bootstrap/cache \
    && chown -R ${USER_ID}:${GROUP_ID} /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Switch to our user for remaining operations
USER ${USER_NAME}

# Install PHP dependencies (including dev for local development)
RUN composer install --optimize-autoloader

# Install Node.js dependencies and build assets
RUN npm install && npm run build

# Switch back to root to start Apache
USER root

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]