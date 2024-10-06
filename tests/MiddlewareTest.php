<?php

/**
 * Flight: An extensible micro-framework.
 *
 * @copyright   Copyright (c) 2012, Mike Cao <mike@mikecao.com>
 * @license     MIT, http://flightphp.com/license
 */

use flight\Flight;

class MiddleWareTest extends \PHPUnit\Framework\TestCase {

    function setUp(): void {
        Flight::init();
    }

    // Map a function
    function testRoute() {

        Flight::route( 'GET /route',
                       function ( $bool_param ) use ( &$has_param ) {
            if ( $has_param !== true ) {
                throw new \Exception( "Param was expected true" );
            }
        }, [ 'inject_param' => true ] );

        $stack = new \flight\LayersStack( true );
        $stack->push( function ( \flight\net\Route $route, array $params, \flight\net\Request $request,
                                 \flight\net\Response $response, \flight\dispatch\DispatchIterator $iterator ) {
            if ( isset( $route->config['inject_param'] ) ) {
                $params[] = true;
            }
            $iterator->next( $route, $params, $request, $response );
        } );
        Flight::map( 'dispatchRoute', [ $stack, 'dispatch' ] );

        Flight::start();

        ob_end_clean();

        $this->assertTrue( true );
    }
}
