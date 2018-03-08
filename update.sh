git fetch origin
git reset --hard origin/master

SYMFONY_ENV=prod ./composer install --no-dev -o --prefer-dist
bin/console doctrine:schema:update

yarn install --modules-folder=web/client/node_modules --production=true
