install dependencies
<br/>
composer install

enable development
<br/>
composer development-enable

create db
<br/>
php data/load_db.php

run test
<br/>
./vendor/bin/phpunit

run application
<br/>
php -S 0.0.0.0:8080 -t public public/index.php
