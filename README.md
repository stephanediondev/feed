# Requirements

- PHP 8.2 with apcu, curl, gmp, iconv, json, mbstring, tidy, xml
- Composer
- MySQL 8
- Yarn

# Installation

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

yarn install
yarn run build
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

yarn install
yarn run build
```

# Screenshots

![iPad item dark](public/screenshots/ipad-item-dark.png)
![iPad feeds search](public/screenshots/ipad-feeds-search.png)
