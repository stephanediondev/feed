git fetch origin
git reset --hard origin/main

composer install
bin/console doctrine:migrations:migrate -n

yarn install
yarn run build

sed -i "s/VERSION =.*/VERSION = '"$(date +%Y-%m-%d.%H-%M-%S)"';/g" public/serviceworker.js
