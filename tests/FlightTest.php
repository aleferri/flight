<?php

/**
 * Flight: An extensible micro-framework.
 *
 * @copyright   Copyright (c) 2012, Mike Cao <mike@mikecao.com>
 * @license     MIT, http://flightphp.com/license
 */
use flight\Flight;

require __DIR__ . "/classes/User.php";

class FlightTest extends \PHPUnit\Framework\TestCase {

    function setUp(): void {
        Flight::init();
    }

    // Checks that default components are loaded
    function testDefaultComponents() {
        $request = Flight::request();
        $response = Flight::response();
        $router = Flight::router();
        $view = Flight::view();

        $this->assertEquals( 'flight\http\Request', get_class( $request ) );
        $this->assertEquals( 'flight\http\Response', get_class( $response ) );
        $this->assertEquals( 'flight\http\Router', get_class( $router ) );
        $this->assertEquals( 'flight\template\View', get_class( $view ) );
    }

    // Test get/set of variables
    function testGetAndSet() {
        $app = Flight::app();

        $app->set( 'a', 1 );
        $var = $app->get( 'a' );

        $this->assertEquals( 1, $var );

        $app->clear();
        $vars = $app->get();

        $this->assertEquals( 0, count( $vars ) );

        $app->set( 'a', 1 );
        $app->set( 'b', 2 );
        $vars = $app->get();

        $this->assertEquals( 2, count( $vars ) );
        $this->assertEquals( 1, $vars[ 'a' ] );
        $this->assertEquals( 2, $vars[ 'b' ] );
    }

    // Register a class
    function testRegister() {
        $app = Flight::app();
        $app->register( 'user', User::class );
        $user = $app->user();

        $this->assertTrue( is_object( $user ) );
        $this->assertEquals( 'User', get_class( $user ) );
    }

    // Map a function
    function testMap() {
        $app = Flight::app();

        $app->map( 'map1', function () {
            return 'hello';
        } );

        $result = $app->map1();

        $this->assertEquals( 'hello', $result );
    }

    function testNamedInstances() {
        $app = Flight::app( 'test', __DIR__ );

        $router = $app->router();
        $another = $app->router( 'another' );

        $this->assertFalse( $router === $another );
    }

    // Unmapped method
    function testUnmapped() {
        $this->expectException( 'Exception' );
        $this->expectExceptionMessage( 'doesNotExist must be a mapped method.' );

        Flight::doesNotExist();
    }

    function testStart() {
        $app = Flight::app( 'test', __DIR__ );

        $app->request()->method = 'GET';
        $app->request()->url = '/uuu';

        $app->route( 'GET /uuu', [ $this, 'sample' ] );

        $app->start();
        $this->expectOutputString( "sample" );
    }

    function sample() {
        echo "sample";
    }

}
