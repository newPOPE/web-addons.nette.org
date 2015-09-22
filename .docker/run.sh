#!/bin/bash

docker run -i -t --rm -v "$PWD:/data" -w /data \
  -p 80:80 \
  nette/addons.nette.org:latest \
  sh -c 'service mysql start;\
    mysqladmin -u root -proot create addons_nette_org;\
    service apache2 start;\
    cd /data && composer install;\
    cp /data/.docker/config/config.local.neon /data/app/config;\
    cd /data && php migrations/run.php structures test-data --reset;\
    bash'
