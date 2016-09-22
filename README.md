####Requirements

#####Language
* PHP 5.5 or greater
* php-curl
* php-iconv
* php-mbstring
* php-tidy (recommended, to cleanup and repair html from feeds)

#####Database
* MySQL 5.5.3 or greater (utf8mb4 character set)

#####Web server
* Apache 2.2 or greater with mod_rewrite module enabled (and "Allowoverride All" in VirtualHost / Directory configuration to allow .htaccess file)
* Nginx (see https://www.nginx.com/resources/wiki/start/topics/recipes/symfony/)

####Installation

```text
cd /path-to-installation
composer install
bin/console doctrine:fixtures:load --append --fixtures="src/Readerself/CoreBundle/DataFixtures"
cd web/client
bower install
```

Add to cron (hourly)
```text
cd /path-to-installation && bin/console readerself:collection
cd /path-to-installation && bin/console readerself:elasticsearch
```

####Update

```text
cd /path-to-installation
composer update
cd web/client
bower update
```

####Client
http://example.com/client
- Email: example@example.com
- Password: example

####Api documentation
http://example.com/api/documentation

####Docker (TODO)
```
cd /path-to-installation
docker-compose build --no-cache --force-rm
docker-compose up -d
```
