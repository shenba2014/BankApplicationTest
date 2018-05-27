install dependencies
<br/>
composer install
<br/>
and then select 1
[1] config/modules.config.php
and then select y
<br/>

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
