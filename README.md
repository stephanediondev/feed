# Requirements

- Web server
- PHP 8.2 with apcu, curl, gmp, iconv, json, mbstring, tidy, xml
- Composer
- MySQL 8

# Installation

## Web server

Configure a vhost with the document root set to the ```public``` folder (ie /var/www/feed/public)

### Apache

Add the vhost below

```
<VirtualHost *:80>
    DocumentRoot "/var/www/feed/public"
    ServerName your-domain-or-sub-domain
    ErrorLog ${APACHE_LOG_DIR}/feed-error.log
    CustomLog ${APACHE_LOG_DIR}/feed-access.log combined

    <Directory /var/www/feed/public>
        AllowOverride None

        DirectoryIndex index.php index.html

        <IfModule mod_negotiation.c>
            Options -MultiViews
        </IfModule>

        <IfModule mod_rewrite.c>
            RewriteEngine On

            RewriteCond %{REQUEST_URI}::$0 ^(/.+)/(.*)::\2$
            RewriteRule .* - [E=BASE:%1]

            RewriteCond %{HTTP:Authorization} .+
            RewriteRule ^ - [E=HTTP_AUTHORIZATION:%0]

            RewriteCond %{ENV:REDIRECT_STATUS} =""
            RewriteRule ^index\.php(?:/(.*)|$) %{ENV:BASE}/$1 [R=301,L]

            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^ %{ENV:BASE}/index.php [L]
        </IfModule>

        <IfModule !mod_rewrite.c>
            <IfModule mod_alias.c>
                RedirectMatch 307 ^/$ /index.php/
            </IfModule>
        </IfModule>
    </Directory>
</VirtualHost>
```

If you can't edit a vhost, add the Apache pack to get the ```.htaccess``` file in the ```public``` folder

```
composer require symfony/apache-pack
```

### nginx

Add the server definition below

```
server {
    listen [::]:8080 default_server;
    listen 8080 default_server;
    server_name your-domain-or-sub-domain;

    sendfile off;

    root /var/www/feed/public;
    index index.php index.html;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;

        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }
}
```

## MySQL user and database

```
mysql -u root -p
```

```sql
CREATE USER 'your-user'@'your-host' IDENTIFIED WITH mysql_native_password BY 'your-password';
CREATE DATABASE IF NOT EXISTS feed DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON feed.* TO 'your-user'@'your-host';
FLUSH PRIVILEGES;
```

## Application

Copy ```.env.dist``` to ```.env```

Edit ```DATABASE_URL=mysql://your-user:your-password@your-host:3306/feed?serverVersion=8&charset=utf8mb4```

```
mkdir config/jwt-keys
openssl genrsa -out config/jwt-keys/application.key 2048
openssl rsa -in config/jwt-keys/application.key -pubout -out config/jwt-keys/application.pub

composer install
bin/console doctrine:schema:create
bin/console app:setup
bin/console app:member:create

bin/console asset-map:compile
```

## Notifications

Edit parameters ```VAPID_*```

```
bin/console app:generate-vapid
```

## Elasticsearch

Edit parameters ```ELASTICSEARCH_*```

```
bin/console app:elasticsearch:create
```

## Commands

```
crontab -e
```

```
# minute (0 - 59) | hour (0 - 23) | day of month (1 - 31) | month (1 - 12) | day of week (1 - 7) | command
0 * * * * cd /path-to-installation && bin/console app:collection
#20 * * * * cd /path-to-installation && bin/console app:notifications
#30 * * * * cd /path-to-installation && bin/console app:elasticsearch
#0 0 * * 7 cd /path-to-installation && bin/console app:maxmind:update
```

# Update

## Application

```
git fetch origin
git reset --hard origin/main

composer install
bin/console doctrine:migrations:migrate -n

bin/console asset-map:compile
```

# Screenshots

![iPad item dark](public/app/images/screenshots/ipad-item-dark.png)
![iPad feeds search](public/app/images/screenshots/ipad-feeds-search.png)
