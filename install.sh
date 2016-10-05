git init
git remote add origin https://github.com/readerself/readerself-symfony.git

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === 'e115a8dc7871f15d853148a7fbac7da27d6c0030b848d9b3dc09e2a0388afed865e6a3d6b3c0fad45c48e2b5fc1196ae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php --filename=composer
php -r "unlink('composer-setup.php');"

SYMFONY_ENV=prod ./composer install --no-dev -o --prefer-dist
bin/console doctrine:fixtures:load --append --fixtures="src/Readerself/CoreBundle/DataFixtures"
cd web/client
npm install -g bower
bower install
cd ../../
