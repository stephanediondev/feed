FROM php:fpm

RUN apt-get update

RUN apt-get install -y libmcrypt-dev libicu-dev libpq-dev libxml2-dev libtidy-dev

RUN docker-php-ext-install iconv mcrypt mbstring intl pdo pdo_mysql tidy

RUN pecl install apcu

RUN pecl install apcu_bc-1.0.3

RUN usermod -u 1000 www-data

CMD ["php-fpm"]
