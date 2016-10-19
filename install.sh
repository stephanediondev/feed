git init
git remote add origin https://github.com/readerself/readerself-symfony.git
git fetch origin
git reset --hard origin/master

EXPECTED_SIGNATURE=$(wget https://composer.github.io/installer.sig -O - -q)
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_SIGNATURE=$(php -r "echo hash_file('SHA384', 'composer-setup.php');")

if [ "$EXPECTED_SIGNATURE" = "$ACTUAL_SIGNATURE" ]
then
    php composer-setup.php --filename=composer --quiet
fi
rm composer-setup.php


SYMFONY_ENV=prod ./composer install --no-dev -o --prefer-dist
bin/console doctrine:fixtures:load --append --fixtures="src/Readerself/CoreBundle/DataFixtures"
cd web/client
npm install -g bower
bower install
cd ../../
