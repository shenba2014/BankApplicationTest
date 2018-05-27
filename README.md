install dependencies
<br/>
composer install

create db
<br/>
php data/load_db.php

run test
<br/>
./vendor/bin/phpunit

run application
<br/>
php -S 0.0.0.0:8080 -t public public/index.php
