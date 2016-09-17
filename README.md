####Requirements

#####Language
* PHP 5.5 or greater
* php-curl
* php-iconv
* php-mbstring
* php-tidy (recommended)

#####Database
* MySQL 5.5.3 or greater (utf8mb4 character set)

#####Web server
* Apache 2.2 or greater with mod_rewrite module enabled (and "Allowoverride All" in VirtualHost / Directory configuration to allow .htaccess file)
* Nginx (see https://www.nginx.com/resources/wiki/start/topics/recipes/symfony/)

####Installation

```text
cd /path-to-installation
composer install
cd client
bower install
```

Add to cron (hourly)
```text
cd /path-to-installation && bin/console readerself:collection
```
