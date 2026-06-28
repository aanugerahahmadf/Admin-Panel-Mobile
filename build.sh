#!/bin/bash

# Install PHP dependencies
composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Install Node dependencies and build assets
npm ci
npm run vite-build

# Generate Filament assets
php artisan filament:assets
