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

## Commands

```
crontab -e
```

```
# m h dom mon dow command
0 * * * * cd /path-to-installation && bin/console app:collection
#30 * * * * cd /path-to-installation && bin/console app:elasticsearch
```

# Update

## Application

```
bin/console doctrine:migrations:migrate -n
```

# Screenshots

![New design](public/screenshots/new-design.png)
