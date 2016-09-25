git fetch origin
git reset --hard origin/master
SYMFONY_ENV=prod ./composer.phar install --no-dev -o --prefer-dist
cd web/client
bower install
cd ../../
