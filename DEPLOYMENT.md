# 🚀 Deployment Guide - Dental Academy

## Production Deployment Checklist

### 1. Server Requirements

#### Minimum Requirements
- **PHP**: 8.0 or higher
- **MySQL/MariaDB**: 5.7+ / 10.3+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 512MB RAM minimum
- **Storage**: 2GB minimum
- **SSL Certificate**: Required for production

#### Recommended Extensions
```bash
php -m | grep -E 'pdo_mysql|gd|mbstring|json|curl|fileinfo'
```

Required PHP extensions:
- `pdo_mysql` - Database connection
- `gd` - Image processing
- `mbstring` - Multi-byte string support
- `json` - JSON encoding/decoding
- `curl` - External API calls
- `fileinfo` - File type detection

### 2. Installation Steps

#### Step 1: Upload Files
```bash
# Via FTP/SFTP
scp -r public_html/* user@server:/var/www/dental-academy/

# Or use Git
git clone https://github.com/yourusername/dental-academy.git
```

#### Step 2: Set Permissions
```bash
cd /var/www/dental-academy

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Writable directories
chmod 777 uploads/
chmod 777 data/
```

#### Step 3: Configure Database
```bash
# Create database
mysql -u root -p
```

```sql
CREATE DATABASE dental_academy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'dental_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON dental_academy.* TO 'dental_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# Import schema
mysql -u dental_user -p dental_academy < database/schema.sql
```

#### Step 4: Update Configuration Files

**database/config.php**
```php
private $host = 'localhost';
private $db_name = 'dental_academy';
private $username = 'dental_user';
private $password = 'YOUR_SECURE_PASSWORD';
```

**Environment settings** (create `config.php` in root)
```php
<?php
define('ENVIRONMENT', 'production');
define('BASE_URL', 'https://dentalacademy.az');
define('UPLOAD_PATH', '/var/www/dental-academy/uploads/');

// Security
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
```

### 3. Apache Configuration

**Create `.htaccess` in root**
```apache
# Enable rewrite engine
RewriteEngine On

# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Prevent directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "^(\.htaccess|\.env|config\.php|composer\.json|composer\.lock|package\.json|README\.md)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType application/pdf "access plus 1 month"
</IfModule>

# Gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css
    AddOutputFilterByType DEFLATE application/javascript application/json
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```

**VirtualHost Configuration**
```apache
<VirtualHost *:80>
    ServerName dentalacademy.az
    ServerAlias www.dentalacademy.az

    Redirect permanent / https://dentalacademy.az/
</VirtualHost>

<VirtualHost *:443>
    ServerName dentalacademy.az
    ServerAlias www.dentalacademy.az

    DocumentRoot /var/www/dental-academy

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/dentalacademy.crt
    SSLCertificateKeyFile /etc/ssl/private/dentalacademy.key
    SSLCertificateChainFile /etc/ssl/certs/dentalacademy-chain.crt

    <Directory /var/www/dental-academy>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # PHP configuration
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value max_execution_time 300
    php_value memory_limit 256M

    # Logging
    ErrorLog ${APACHE_LOG_DIR}/dental-academy-error.log
    CustomLog ${APACHE_LOG_DIR}/dental-academy-access.log combined
</VirtualHost>
```

### 4. Nginx Configuration (Alternative)

```nginx
server {
    listen 80;
    server_name dentalacademy.az www.dentalacademy.az;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name dentalacademy.az www.dentalacademy.az;

    root /var/www/dental-academy;
    index index.html index.php;

    # SSL
    ssl_certificate /etc/ssl/certs/dentalacademy.crt;
    ssl_certificate_key /etc/ssl/private/dentalacademy.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Gzip
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml;

    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ /(composer\.json|package\.json|README\.md|DEPLOYMENT\.md)$ {
        deny all;
    }
}
```

### 5. SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Apache
sudo certbot --apache -d dentalacademy.az -d www.dentalacademy.az

# Nginx
sudo certbot --nginx -d dentalacademy.az -d www.dentalacademy.az

# Auto-renewal
sudo certbot renew --dry-run
```

### 6. Email Configuration (PHPMailer)

**For Gmail (with App Password)**
```php
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password'; // Not your Gmail password!
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
```

**For Mailgun**
```php
$mail->isSMTP();
$mail->Host = 'smtp.mailgun.org';
$mail->SMTPAuth = true;
$mail->Username = 'postmaster@mg.dentalacademy.az';
$mail->Password = 'your-mailgun-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
```

### 7. Cron Jobs (Automated Tasks)

```bash
crontab -e
```

Add these cron jobs:
```bash
# Backup database daily at 2 AM
0 2 * * * /usr/bin/mysqldump -u dental_user -p'PASSWORD' dental_academy > /backups/dental_$(date +\%Y\%m\%d).sql

# Clean old uploaded files (older than 30 days)
0 3 * * * find /var/www/dental-academy/uploads -type f -mtime +30 -delete

# Generate sitemap weekly
0 4 * * 0 /usr/bin/php /var/www/dental-academy/scripts/generate-sitemap.php

# Send course reminders
0 9 * * * /usr/bin/php /var/www/dental-academy/scripts/send-reminders.php
```

### 8. Monitoring & Logging

**Setup Log Rotation**
```bash
sudo nano /etc/logrotate.d/dental-academy
```

```
/var/www/dental-academy/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

**Monitor with Uptime Kuma / UptimeRobot**
- Monitor: https://dentalacademy.az
- Check interval: 5 minutes
- Alert via email/Telegram

### 9. Performance Optimization

**Install OPcache**
```bash
sudo apt install php8.1-opcache
```

```ini
; /etc/php/8.1/apache2/conf.d/10-opcache.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
```

**Enable MySQL Query Cache**
```sql
SET GLOBAL query_cache_type = ON;
SET GLOBAL query_cache_size = 67108864; -- 64MB
```

### 10. Security Hardening

**Disable unnecessary PHP functions**
```ini
; /etc/php/8.1/apache2/php.ini
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source
```

**Install Fail2Ban**
```bash
sudo apt install fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

**Configure Firewall**
```bash
sudo ufw allow 22/tcp   # SSH
sudo ufw allow 80/tcp   # HTTP
sudo ufw allow 443/tcp  # HTTPS
sudo ufw enable
```

### 11. Backup Strategy

**Automated Backup Script**
```bash
#!/bin/bash
# /var/www/dental-academy/scripts/backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/dental-academy"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u dental_user -p'PASSWORD' dental_academy | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/dental-academy \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='uploads/temp'

# Keep only last 30 days
find $BACKUP_DIR -type f -mtime +30 -delete

# Upload to cloud (optional)
# rclone copy $BACKUP_DIR remote:backups/dental-academy
```

Make executable:
```bash
chmod +x /var/www/dental-academy/scripts/backup.sh
```

### 12. Testing Post-Deployment

**Checklist:**
- [ ] Website loads via HTTPS
- [ ] All images display correctly
- [ ] Forms submit successfully
- [ ] Email notifications work
- [ ] Database connections successful
- [ ] File uploads functional
- [ ] Dark/Light mode toggles
- [ ] Responsive on mobile devices
- [ ] Admin panel accessible
- [ ] Badge generator works
- [ ] API endpoints respond
- [ ] No console errors

**Performance Testing:**
```bash
# PageSpeed Insights
https://pagespeed.web.dev/

# GTmetrix
https://gtmetrix.com/

# SSL Labs
https://www.ssllabs.com/ssltest/
```

### 13. Go Live!

1. Update DNS records
2. Clear all caches
3. Test thoroughly
4. Monitor error logs
5. Announce launch!

---

## Rollback Plan

If something goes wrong:
```bash
# Restore database
mysql -u dental_user -p dental_academy < /backups/dental_20251129.sql

# Restore files
tar -xzf /backups/files_20251129.tar.gz -C /

# Restart services
sudo systemctl restart apache2
sudo systemctl restart mysql
```

## Support Contacts

- **Technical Issues**: support@dentalacademy.az
- **Emergency**: +994 XX XXX XX XX
- **Server Provider**: [Your hosting provider]

---

**Deployment Date**: 2025-11-29
**Deployed By**: System Administrator
**Version**: 2.0.0
