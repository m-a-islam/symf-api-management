# Stage 1: The Final Development Dockerfile

# Use a lightweight Alpine Linux image with PHP-FPM. It's smaller and faster.
FROM php:8.2-fpm-alpine

# Set the working directory inside the container. All subsequent commands run here.
WORKDIR /var/www/html

# --- System Dependencies ---
# Install necessary system packages. Alpine uses 'apk' instead of 'apt-get'.
# git, zip/unzip are for Composer. icu-dev is required for the 'intl' PHP extension.
# --no-cache is an Alpine best practice to keep the image small.
RUN apk add --no-cache \
    git \
    icu-dev \
    icu-data-full \
    zip \
    unzip \
    bash

# --- PHP Extensions ---
# Install the PHP extensions that Symfony and our application need.
# pdo_mysql: For connecting to our MySQL database.
# intl: For internationalization features, required by Symfony.
# opcache: A performance booster that caches precompiled script bytecode.
RUN docker-php-ext-install pdo_mysql intl opcache

# --- Composer Installation ---
# Install Composer (the PHP package manager) using a clean, multi-stage copy.
# This is safer and cleaner than using curl installers.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --- Dependency Installation (The Caching Trick) ---
# 1. Copy only the dependency definition files first.
# This leverages Docker's layer caching. As long as these files don't change,
# Docker will NOT re-download all your vendors on every build, saving a lot of time.
COPY src/composer.json src/composer.lock ./

# 2. Install the dependencies.
# --prefer-dist: Downloads zipped versions, which is faster.
# --no-scripts: We'll run scripts later, after our code is present.
# --no-interaction: Prevents the command from asking interactive questions.
RUN composer install --prefer-dist --no-scripts --no-progress --no-interaction

# --- Application Code ---
# Now, copy the rest of your application source code into the working directory.
COPY src/. .

# --- Finalizing Installation ---
# Generate the optimized autoloader and run any Symfony scripts (like cache warming).
# This needs to run *after* the full source code has been copied.
RUN composer dump-autoload --optimize && \
    composer run-script post-install-cmd

# --- Permissions ---
# Set the correct permissions for the cache and log directories.
# The user 'www-data' is the default user that the PHP-FPM process runs as.
# This prevents "Permission Denied" errors when Symfony tries to write files.
RUN chown -R www-data:www-data .

# --- Final Command ---
# The base php-fpm image already sets the correct CMD to start the service.
# This line is not strictly necessary but makes it explicit.

# Expose the PHP-FPM port
EXPOSE 9000

# The command to run the application
CMD ["php-fpm"]