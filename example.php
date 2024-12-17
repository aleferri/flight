<?php

require "vendor/autoload.php";

use flight\Flight;

$app = Flight::app( 'test', __DIR__ );

$router = $app->router();
$router->get( '/', function ($request) use ($app) {
    error_log( json_encode( $_SERVER ) );

    if ( isset( $_SERVER[ 'HTTP_ORIGIN' ] ) || isset( $_SERVER[ 'HTTP_SEC_FETCH_SITE' ] ) ) {
        return $app->allow_cross_origin( $request );
    }
    echo 'hello world!';
}, [ 'share' => '*' ] );

$app->start();
