git fetch origin
git reset --hard origin/master

SYMFONY_ENV=prod ./composer update --no-dev -o --prefer-dist
bin/console doctrine:schema:update

cd web/client
bower update
cd ../../
