<?php

require 'flight/Flight.php';

$app = Flight::app( 'test', __DIR__ );

$router = $app->router();
$router->get( '/', function () {
    echo 'hello world!';
} );

$router = $app->router( 'customer_name' );

$app->start();
