git fetch origin
git reset --hard origin/main

SYMFONY_ENV=prod ./composer install --no-dev -o --prefer-dist
bin/console doctrine:migrations:migrate -n

rm -rf web/client/node_modules
yarn install --modules-folder=web/client/node_modules --production=true
