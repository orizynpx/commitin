#!/bin/bash

echo "Starting Azure App Service Custom Startup Script..."

# Copy custom Nginx configuration to all potential defaults
if [ -f /home/site/wwwroot/.azure/nginx.conf ]; then
    echo "Applying custom Nginx configuration..."
    
    # Try copying to all common Nginx default locations
    cp /home/site/wwwroot/.azure/nginx.conf /etc/nginx/sites-available/default
    cp /home/site/wwwroot/.azure/nginx.conf /etc/nginx/sites-enabled/default
    cp /home/site/wwwroot/.azure/nginx.conf /etc/nginx/conf.d/default.conf
    
    # Test Nginx configuration
    echo "Testing Nginx configuration..."
    nginx -t
    
    # Reload Nginx using multiple methods to ensure it works across container versions
    echo "Reloading Nginx..."
    nginx -s reload || service nginx reload || service nginx restart
else
    echo "WARNING: Custom Nginx configuration not found at /home/site/wwwroot/.azure/nginx.conf"
fi

# Ensure default Laravel env file exists (Azure App settings overrides it)
if [ ! -f /home/site/wwwroot/.env ]; then
    echo "Creating empty .env file..."
    touch /home/site/wwwroot/.env
fi

# Set up persistent storage for uploaded files so they don't get deleted on deploy
echo "Setting up persistent upload storage..."
mkdir -p /home/site/storage/app/public
rm -rf /home/site/wwwroot/storage/app/public
ln -s /home/site/storage/app/public /home/site/wwwroot/storage/app/public

# Re-link Laravel public storage symlink
echo "Linking storage to public..."
php /home/site/wwwroot/artisan storage:link --force

# Set up storage folder structures and permissions
echo "Ensuring storage structure and permissions..."
mkdir -p /home/site/wwwroot/storage/framework/cache/data
mkdir -p /home/site/wwwroot/storage/framework/sessions
mkdir -p /home/site/wwwroot/storage/framework/views
mkdir -p /home/site/wwwroot/storage/logs
chmod -R 775 /home/site/wwwroot/storage
chmod -R 775 /home/site/wwwroot/bootstrap/cache
chmod -R 775 /home/site/storage

# If SQLite is selected as DB_CONNECTION (default fallback in config/database.php)
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    SQLITE_PATH="${DB_DATABASE:-/home/database.sqlite}"
    echo "Using SQLite database at: $SQLITE_PATH"
    if [ ! -f "$SQLITE_PATH" ]; then
        echo "Creating empty SQLite database file..."
        touch "$SQLITE_PATH"
    fi
    # Make sure SQLite file has correct permissions
    chmod 666 "$SQLITE_PATH"
fi

# Optimize Laravel installation
echo "Running Laravel production optimization caching..."
php /home/site/wwwroot/artisan config:cache
php /home/site/wwwroot/artisan route:cache
php /home/site/wwwroot/artisan view:cache

# Run database migrations
echo "Running database migrations..."
php /home/site/wwwroot/artisan migrate --force

echo "Azure App Service Custom Startup Script finished!"
