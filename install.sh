SYMFONY_ENV=prod composer install --no-dev -o --prefer-dist
bin/console doctrine:fixtures:load --append --fixtures="src/Readerself/CoreBundle/DataFixtures"
cd web/client
bower install
cd ../../
