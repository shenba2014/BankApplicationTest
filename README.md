<h2>Steps to run it:</h2>

<strong>install dependencies</strong>
<br/>
composer install
<br/>
and then select 1
<br/>
[1] config/modules.config.php
<br/>
and then select y
<br/>

<strong>enable development</strong>
<br/>
composer development-enable

<strong>create db</strong>
<br/>
php data/load_db.php

<strong>run test</strong>
<br/>
./vendor/bin/phpunit

<strong>run application</strong>
<br/>
php -S 0.0.0.0:8080 -t public public/index.php
