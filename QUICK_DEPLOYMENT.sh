#!/bin/bash

# 🚀 Advanced Coupon System - Quick Deployment Script
# استخدام: sudo bash QUICK_DEPLOYMENT.sh

set -e

echo "================================"
echo "🚀 Advanced Coupon System Setup"
echo "================================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Variables
PROJECT_NAME="AdvancedCouponSystem"
PROJECT_DIR="/var/www/$PROJECT_NAME"
DB_NAME="advanced_coupon_system"
DB_USER="coupon_user"

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Please run as root (use sudo)${NC}"
    exit 1
fi

echo -e "${GREEN}Step 1: Installing System Dependencies${NC}"
apt update && apt upgrade -y
apt install -y software-properties-common

echo -e "${GREEN}Step 2: Installing PHP 8.2${NC}"
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-intl \
    php8.2-bcmath php8.2-redis

echo -e "${GREEN}Step 3: Installing Composer${NC}"
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi

echo -e "${GREEN}Step 4: Installing Node.js 20.x${NC}"
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt install -y nodejs
fi

echo -e "${GREEN}Step 5: Installing MySQL${NC}"
apt install -y mysql-server

echo -e "${GREEN}Step 6: Installing Nginx${NC}"
apt install -y nginx

echo -e "${GREEN}Step 7: Installing Supervisor${NC}"
apt install -y supervisor

echo -e "${GREEN}Step 8: Installing Redis (Optional)${NC}"
apt install -y redis-server
systemctl enable redis-server
systemctl start redis-server

echo ""
echo -e "${YELLOW}=== Database Setup ===${NC}"
read -p "Enter MySQL root password: " -s MYSQL_ROOT_PASS
echo ""
read -p "Enter password for database user '$DB_USER': " -s DB_PASS
echo ""

# Create database and user
mysql -u root -p"$MYSQL_ROOT_PASS" <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

echo -e "${GREEN}Database created successfully!${NC}"

echo ""
echo -e "${YELLOW}=== Project Setup ===${NC}"

# Check if project directory exists
if [ ! -d "$PROJECT_DIR" ]; then
    echo -e "${RED}Project directory not found: $PROJECT_DIR${NC}"
    echo "Please upload your project to $PROJECT_DIR first"
    exit 1
fi

cd "$PROJECT_DIR"

echo -e "${GREEN}Step 9: Installing Composer Dependencies${NC}"
composer install --optimize-autoloader --no-dev

echo -e "${GREEN}Step 10: Installing NPM Dependencies${NC}"
npm install

echo -e "${GREEN}Step 11: Building Assets${NC}"
npm run build

echo -e "${GREEN}Step 12: Setting up .env file${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    
    # Update .env with database credentials
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env
    sed -i "s/APP_ENV=.*/APP_ENV=production/" .env
    sed -i "s/APP_DEBUG=.*/APP_DEBUG=false/" .env
fi

echo -e "${GREEN}Step 13: Generating Application Key${NC}"
php artisan key:generate --force

echo -e "${GREEN}Step 14: Running Migrations${NC}"
php artisan migrate --force

echo -e "${GREEN}Step 15: Creating Storage Link${NC}"
php artisan storage:link

echo -e "${GREEN}Step 16: Caching Configuration${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo -e "${GREEN}Step 17: Setting Permissions${NC}"
chown -R www-data:www-data "$PROJECT_DIR"
chmod -R 755 "$PROJECT_DIR"
chmod -R 775 "$PROJECT_DIR/storage"
chmod -R 775 "$PROJECT_DIR/bootstrap/cache"
chmod 600 "$PROJECT_DIR/.env"

echo -e "${GREEN}Step 18: Configuring Nginx${NC}"
read -p "Enter your domain name (e.g., example.com): " DOMAIN

cat > /etc/nginx/sites-available/$PROJECT_NAME <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN www.$DOMAIN;
    root $PROJECT_DIR/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

ln -sf /etc/nginx/sites-available/$PROJECT_NAME /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

nginx -t && systemctl restart nginx

echo -e "${GREEN}Step 19: Configuring Supervisor for Queue${NC}"
cat > /etc/supervisor/conf.d/$PROJECT_NAME-queue.conf <<EOF
[program:$PROJECT_NAME-queue]
process_name=%(program_name)s_%(process_num)02d
command=php $PROJECT_DIR/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=$PROJECT_DIR/storage/logs/queue-worker.log
stopwaitsecs=3600
EOF

supervisorctl reread
supervisorctl update
supervisorctl start $PROJECT_NAME-queue:*

echo -e "${GREEN}Step 20: Setting up Cron${NC}"
(crontab -u www-data -l 2>/dev/null; echo "* * * * * cd $PROJECT_DIR && php artisan schedule:run >> /dev/null 2>&1") | crontab -u www-data -

echo -e "${GREEN}Step 21: Configuring Firewall${NC}"
ufw allow OpenSSH
ufw allow 'Nginx Full'
echo "y" | ufw enable

echo ""
echo -e "${GREEN}================================${NC}"
echo -e "${GREEN}✅ Installation Complete!${NC}"
echo -e "${GREEN}================================${NC}"
echo ""
echo -e "🌐 Your site is available at: ${YELLOW}http://$DOMAIN${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Visit http://$DOMAIN to see your site"
echo "2. Install SSL with: sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN"
echo "3. Create admin user: cd $PROJECT_DIR && php artisan tinker"
echo ""
echo -e "${YELLOW}Useful commands:${NC}"
echo "- View logs: tail -f $PROJECT_DIR/storage/logs/laravel.log"
echo "- Check queue: sudo supervisorctl status"
echo "- Clear cache: cd $PROJECT_DIR && php artisan cache:clear"
echo ""

