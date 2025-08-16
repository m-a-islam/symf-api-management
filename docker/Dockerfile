# Stage 1: The "builder" stage
# We use an official composer image to install dependencies
FROM composer:2 as builder

WORKDIR /app
# Copy only the composer files first to leverage Docker's layer caching
COPY src/composer.json src/composer.lock ./
# Install dependencies, --no-dev for production
RUN composer install --no-dev --no-scripts --optimize-autoloader

# Copy the rest of the application source code
COPY src/. .
# Run scripts like warming up the cache
RUN composer run-script post-install-cmd


# Stage 2: The "final" stage
# We use our lean php-fpm image
FROM php:8.2-fpm

WORKDIR /var/www/html

# Install only the necessary PHP extensions for running the app
RUN apt-get update && apt-get install -y libzip-dev libicu-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl

# Copy the installed vendor directory and the source code from the "builder" stage
COPY --from=builder /app/vendor/ ./vendor/
COPY --from=builder /app/ .

# Set correct permissions
RUN chown -R www-data:www-data .

# Expose the PHP-FPM port
EXPOSE 9000

# The command to run the application
CMD ["php-fpm"]