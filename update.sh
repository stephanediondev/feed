git fetch origin
git reset --hard origin/master

SYMFONY_ENV=prod ./composer install --no-dev -o --prefer-dist
bin/console doctrine:schema:update

cd web/client
yarn update
cd ../../
