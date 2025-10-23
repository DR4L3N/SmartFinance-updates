FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_sqlite mbstring exif pcntl bcmath gd

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get update && apt-get install -y nodejs

# Clear cache to reduce layer size
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy your Laravel application
COPY . .

# Run setup script (configured to run npm, composer, etc.)
RUN chmod +x setup.sh && ./setup.sh

# Set permissions for Laravel cache and storage
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose port 8000 for Laravel's development server
EXPOSE 8000

# Set environment variables (optional)
ENV APP_ENV=production
ENV PORT=8000

# Start Laravel app using artisan serve
CMD php artisan serve --host=0.0.0.0 --port=$PORT
