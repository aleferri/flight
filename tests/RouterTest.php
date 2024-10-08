<?php

/**
 * Flight: An extensible micro-framework.
 *
 * @copyright   Copyright (c) 2011, Mike Cao <mike@mikecao.com>
 * @license     MIT, http://flightphp.com/license
 */
use flight\Flight;

class RouterTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var \flight\net\Router
     */
    private $router;

    /**
     * @var \flight\net\Request
     */
    private $request;

    /**
     * @var \flight\core\Dispatcher
     */
    private $dispatcher;

    function setUp(): void {
        $this->router = new \flight\http\Router();
        $this->request = new \flight\http\Request();
        $this->dispatcher = new \flight\core\Dispatcher();
    }

    // Simple output
    function ok() {
        return Flight::response()->write( 'OK' );
    }

    // Checks if a route was matched with a given output
    function check($str = '') {
        $response = $this->routeRequest();
        $response->send();

        $this->expectOutputString( $str );
    }

    function routeRequest() {
        $dispatched = false;

        while ( $route = $this->router->route( $this->request ) ) {
            $params = array_values( $route->params );

            if ( isset( $route->config[ 'pass_route' ] ) && $route->config[ 'pass_route' ] ) {
                $params[] = $route;
            }

            $response = ($route->callback)( ...$params );

            $dispatched = $response->is_complete();

            if ( $dispatched ) {
                break;
            }

            $this->router->next();
        }

        if ( ! $dispatched ) {
            return Flight::response()->status( 404 )->write( '404' );
        }

        return $response;
    }

    // Default route
    function testDefaultRoute() {
        $this->router->map( '/', array( $this, 'ok' ) );
        $this->request->url = '/';

        $this->check( 'OK' );
    }

    // Simple path
    function testPathRoute() {
        $this->router->map( '/path', array( $this, 'ok' ) );
        $this->request->url = '/path';

        $this->check( 'OK' );
    }

    // POST route
    function testPostRoute() {
        $this->router->map( 'POST /', array( $this, 'ok' ) );
        $this->request->url = '/';
        $this->request->method = 'POST';

        $this->check( 'OK' );
    }

    // Either GET or POST route
    function testGetPostRoute() {
        $this->router->map( 'GET|POST /', array( $this, 'ok' ) );
        $this->request->url = '/';
        $this->request->method = 'GET';

        $this->check( 'OK' );
    }

    // Test regular expression matching
    function testRegEx() {
        $this->router->map( '/num/[0-9]+', array( $this, 'ok' ) );
        $this->request->url = '/num/1234';

        $this->check( 'OK' );
    }

    // Passing URL parameters
    function testUrlParameters() {
        $this->router->map( '/user/@id', function ($id) {
            return Flight::response()->write( $id );
        } );
        $this->request->url = '/user/123';

        $this->check( '123' );
    }

    // Passing URL parameters matched with regular expression
    function testRegExParameters() {
        $this->router->map( '/test/@name:[a-z]+', function ($name) {
            return Flight::response()->write( $name );
        } );
        $this->request->url = '/test/abc';

        $this->check( 'abc' );
    }

    // Optional parameters
    function testOptionalParameters() {
        $this->router->map(
            '/blog(/@year(/@month(/@day)))',
            function ($year, $month, $day) {
                return Flight::response()->write( "$year,$month,$day" );
            }
        );
        $this->request->url = '/blog/2000';

        $this->check( '2000,,' );
    }

    // Regex in optional parameters
    function testRegexOptionalParameters() {
        $this->router->map(
            '/@controller/@method(/@id:[0-9]+)',
            function ($controller, $method, $id) {
                return Flight::response()->write( "$controller,$method,$id" );
            }
        );
        $this->request->url = '/user/delete/123';

        $this->check( 'user,delete,123' );
    }

    // Regex in optional parameters
    function testRegexEmptyOptionalParameters() {
        $this->router->map(
            '/@controller/@method(/@id:[0-9]+)',
            function ($controller, $method, $id) {
                return Flight::response()->write( "$controller,$method,$id" );
            }
        );
        $this->request->url = '/user/delete/';

        $this->check( 'user,delete,' );
    }

    // Wildcard matching
    function testWildcard() {
        $this->router->map( '/account/*', array( $this, 'ok' ) );
        $this->request->url = '/account/123/abc/xyz';

        $this->check( 'OK' );
    }

    // Check if route object was passed
    function testRouteObjectPassing() {
        $this->router->map(
            '/yes_route',
            function ($route) {
                $this->assertTrue( is_object( $route ) );
                $this->assertTrue( is_array( $route->methods ) );
                $this->assertTrue( is_array( $route->params ) );
                $this->assertEquals( sizeof( $route->params ), 0 );
                $this->assertEquals( $route->regex, null );
                $this->assertEquals( $route->splat, '' );
                $this->assertTrue( $route->config[ 'pass_route' ] );

                return Flight::response()->status( 200 );
            }, [ 'pass_route' => true ]
        );
        $this->request->url = '/yes_route';

        $this->check();

        $this->router->map(
            '/no_route',
            function ($route = null) {
                $this->assertTrue( is_null( $route ) );

                return Flight::response()->status( 200 );
            }, [ 'pass_route' => false ]
        );
        $this->request->url = '/no_route';

        $this->check();
    }

    function testRouteWithParameters() {
        $this->router->map(
            '/@one/@two',
            function ($one, $two, $route) {
                $this->assertEquals( sizeof( $route->params ), 2 );
                $this->assertEquals( $route->params[ 'one' ], $one );
                $this->assertEquals( $route->params[ 'two' ], $two );

                return Flight::response()->status( 200 );
            }, [ 'pass_route' => true ]
        );
        $this->request->url = '/1/2';

        $this->check();
    }

    // Test splat
    function testSplatWildcard() {
        $this->router->map( '/account/*', function ($route) {
            return Flight::response()->status( 200 )->write( $route->splat );
        }, [ 'pass_route' => true ] );
        $this->request->url = '/account/456/def/xyz';

        $this->check( '456/def/xyz' );
    }

    // Test splat without trailing slash
    function testSplatWildcardTrailingSlash() {
        $this->router->map( '/account/*', function ($route) {
            return Flight::response()->status( 200 )->write( $route->splat );
        }, [ 'pass_route' => true ] );
        $this->request->url = '/account';

        $this->check();
    }

    // Test splat with named parameters
    function testSplatNamedPlusWildcard() {
        $this->router->map(
            '/account/@name/*',
            function ($name, $route) {
                $this->assertEquals( 'abc', $name );

                return Flight::response()->status( 200 )->write( $route->splat );
            }, [ 'pass_route' => true ]
        );
        $this->request->url = '/account/abc/456/def/xyz';

        $this->check( '456/def/xyz' );
    }

    // Test not found
    function testNotFound() {
        $this->router->map( '/does_exist', array( $this, 'ok' ) );
        $this->request->url = '/does_not_exist';

        $this->check( '404' );
    }

    // Test case sensitivity
    function testCaseSensitivity() {
        $this->router->map( '/hello', array( $this, 'ok' ) );
        $this->request->url = '/HELLO';
        $this->router->case_sensitive = true;

        $this->check( '404' );
    }

    // Passing URL parameters matched with regular expression for a URL containing Cyrillic letters:
    function testRegExParametersCyrillic() {
        $this->router->map(
            '/категория/@name:[абвгдеёжзийклмнопрстуфхцчшщъыьэюя]+',
            function ($name) {
                return Flight::response()->status( 200 )->write( $name );
            }
        );
        $this->request->url = urlencode( '/категория/цветя' );

        $this->check( 'цветя' );
    }

}
