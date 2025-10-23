#!/bin/bash

composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite && php artisan migrate:fresh --seed
npm run build
