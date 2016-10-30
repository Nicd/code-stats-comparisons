<?php

require_once 'utils.php';
require_once 'config.php';
require_once 'db.php';
require_once 'router.php';
require_once 'routes.php';

$db = new DB(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS);

$router = new Router(ROUTES, $db);
$router->run();
