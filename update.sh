git fetch origin
git reset --hard origin/main

current_timestamp=`date +%Y%m%d.%H%M%S`
sed -i "s/VERSION =.*/VERSION = '${current_timestamp}';/g" public/client/serviceworker.js

SYMFONY_ENV=prod composer install --no-dev -o --prefer-dist
bin/console doctrine:migrations:migrate -n

rm -rf public/client/node_modules
yarn install --modules-folder=public/client/node_modules --production=true
