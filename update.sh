git fetch
SYMFONY_ENV=prod composer update --no-dev -o --prefer-dist
cd web/client
bower update
cd ../../
