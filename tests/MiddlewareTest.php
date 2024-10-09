<?php

/**
 * Flight: An extensible micro-framework.
 *
 * @copyright   Copyright (c) 2012, Mike Cao <mike@mikecao.com>
 * @license     MIT, http://flightphp.com/license
 */
use flight\Flight;

class MiddlewareTest extends \PHPUnit\Framework\TestCase {

    function setUp(): void {

    }

    // Map a function
    function testRoute() {
        $app = Flight::app();

        $app->route(
            'GET /route',
            function ($request, $bool_param) {
                if ( $bool_param !== true ) {
                    throw new \RuntimeException( "Param was expected true" );
                }

                return Flight::response()->status( 200 )->write( 'ok' );
            }, [ 'inject_param' => true ]
        );

        $stack = new \flight\LayersStack( $app, true );
        $stack->push( function (
            \flight\http\Route $route, \flight\http\Request $request,
            array $params, \flight\dispatch\DispatchIterator $iterator
        ) {
            if ( isset( $route->config[ 'inject_param' ] ) ) {
                $params[] = true;
            }

            return $iterator->next( $route, $request, $params );
        } );

        $app->map( 'dispatchRoute', [ $stack, 'dispatch' ] );
        $app->request()->method = 'GET';
        $app->request()->url = '/route';

        $app->start();
        $this->expectOutputString( 'ok' );

        //error_log( ob_get_level() );
    }

}
