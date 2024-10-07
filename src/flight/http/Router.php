<?php

/**
 * Flight: An extensible micro-framework.
 *
 * @copyright   Copyright (c) 2011, Mike Cao <mike@mikecao.com>
 * @license     MIT, http://flightphp.com/license
 */

namespace flight\http;

/**
 * The Router class is responsible for routing an HTTP request to
 * an assigned callback function. The Router tries to match the
 * requested URL against a series of URL patterns.
 */
class Router {

    /**
     * Mapped routes.
     *
     * @var array
     */
    protected $routes;

    /**
     * Pointer to current route.
     *
     * @var int
     */
    protected $index;

    /**
     * Case sensitive matching.
     *
     * @var boolean
     */
    public $case_sensitive;

    public function __construct() {
        $this->routes = [
            'GET'     => [],
            'POST'    => [],
            'PUT'     => [],
            'PATCH'   => [],
            'DELETE'  => [],
            'OPTIONS' => [],
            'HEAD'    => []
        ];
        $this->index = 0;
        $this->case_sensitive = false;
    }

    /**
     * Gets mapped routes.
     *
     * @return array Array of routes
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * Clears all routes in the router.
     */
    public function clear() {
        $this->routes = array();
    }

    /**
     * Maps a URL pattern to a callback function.
     *
     * @param string $pattern URL pattern to match
     * @param callback $callback Callback function
     * @param array $config Pass the matching route object to the callback
     */
    public function map( $pattern, $callback, array $config = [] ): Route {
        $url = trim( $pattern );
        $methods = [ 'GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD' ];

        if ( strpos( $url, ' ' ) !== false ) {
            list($method, $url) = explode( ' ', $url, 2 );
            $url = trim( $url );
            $methods = explode( '|', $method );
        }

        $route = new Route( $url, $callback, $methods, $config );
        foreach ( $methods as $method ) {
            $this->routes[$method][] = $route;
        }
        
        return $route;
    }

    /**
     * Maps a HEAD URL pattern to a callback function.
     *
     * @param string $pattern URL pattern to match
     * @param callback $callback Callback function
     * @param array $config Pass the matching route object to the callback
     */
    public function head( $pattern, $callback, array $config = [] ): Route {
        return $this->map( 'HEAD ' . $pattern, $callback, $config );
    }
    
    /**
     * Maps a GET URL pattern to a callback function.
     *
     * @param string $pattern URL pattern to match
     * @param callback $callback Callback function
     * @param array $config Pass the matching route object to the callback
     */
    public function get( $pattern, $callback, array $config = [] ): Route {
        return $this->map( 'GET ' . $pattern, $callback, $config );
    }
    
    /**
     * Maps a POST URL pattern to a callback function.
     *
     * @param string $pattern URL pattern to match
     * @param callback $callback Callback function
     * @param array $config Pass the matching route object to the callback
     */
    public function post( $pattern, $callback, array $config = [] ): Route {
        return $this->map( 'POST ' . $pattern, $callback, $config );
    }
    
    /**
     * Maps a PUT URL pattern to a callback function.
     *
     * @param string $pattern URL pattern to match
     * @param callback $callback Callback function
     * @param array $config Pass the matching route object to the callback
     */
    public function put( $pattern, $callback, array $config = [] ): Route {
        return $this->map( 'PUT ' . $pattern, $callback, $config );
    }
    
    /**
     * Maps a DELETE URL pattern to a callback function.
     *
     * @param string $pattern URL pattern to match
     * @param callback $callback Callback function
     * @param array $config Pass the matching route object to the callback
     */
    public function delete( $pattern, $callback, array $config = [] ): Route {
        return $this->map( 'DELETE ' . $pattern, $callback, $config );
    }

    /**
     * Routes the current request.
     *
     * @param Request $request Request object
     * @return Route|bool Matching route or false if no match
     */
    public function route( Request $request ) {
        $url_decoded = urldecode( $request->url );

        $bucket = $this->routes[$request->method];

        foreach ( $bucket as $route ) {
            if ( $route->matchUrl( $url_decoded, $this->case_sensitive ) ) {
                return $route;
            }
        }

        return false;
    }
}
