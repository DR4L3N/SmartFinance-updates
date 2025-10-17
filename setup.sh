#!/bin/bash

echo "🚀 Setting up SmartFinance project..."

# Check if .env file exists, if not create it from example
if [ ! -f .env ]; then
    echo "📄 Creating .env file..."
    cp .env.example .env
    echo "✅ Created .env file"
fi

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install
echo "✅ PHP dependencies installed"

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate
echo "✅ Application key generated"

# Create SQLite database
echo "🗄️ Setting up database..."
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    echo "✅ Created SQLite database file"
fi

# Run database migrations
echo "🔄 Running database migrations..."
php artisan migrate
echo "✅ Database migrations completed"

# Install NPM dependencies and build assets
echo "📦 Installing NPM dependencies..."
npm install
echo "✅ NPM dependencies installed"

echo "🏗️ Building frontend assets..."
npm run build
echo "✅ Frontend assets built"

# Clear cache
echo "🧹 Clearing application cache..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear
echo "✅ Cache cleared"

# Set storage permissions
echo "🔒 Setting storage permissions..."
chmod -R 775 storage bootstrap/cache
echo "✅ Permissions set"

echo "
✨ Setup complete! Your application is ready.
🌐 To start the application:
   php artisan serve

Then visit: http://localhost:8000"
