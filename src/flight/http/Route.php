<?php

/**
 * Flight: An extensible micro-framework.
 *
 * @copyright   Copyright (c) 2011, Mike Cao <mike@mikecao.com>
 * @license     MIT, http://flightphp.com/license
 */

namespace flight\http;

/**
 * The Route class is responsible for routing an HTTP request to
 * an assigned callback function. The Router tries to match the
 * requested URL against a series of URL patterns.
 */
class Route {

    /**
     * @var string URL pattern
     */
    public $pattern;

    /**
     * @var mixed Callback function
     */
    public $callback;

    /**
     * @var array HTTP methods
     */
    public $methods;

    /**
     * @var array Route parameters
     */
    public $params = array();

    /**
     * @var string Matching regular expression
     */
    public $regex;

    /**
     * @var string URL splat content
     */
    public $splat = '';

    /**
     * @var array Additional parameters for middlewares
     */
    public $config;

    /**
     * Constructor.
     *
     * @param string $pattern URL pattern
     * @param mixed $callback Callback function
     * @param array $methods HTTP methods
     * @param array $config additional parameters for middlewares
     */
    public function __construct( $pattern, $callback, array $methods, array $config ) {
        $this->pattern  = $pattern;
        $this->callback = $callback;
        $this->methods  = $methods;
        $this->config   = $config;
    }

    /**
     * Checks if a URL matches the route pattern. Also parses named parameters in the URL.
     *
     * @param string $url Requested URL
     * @param boolean $case_sensitive Case sensitive matching
     * @return boolean Match status
     */
    public function matchUrl( $url, $case_sensitive = false ) {
        // Wildcard or exact match
        if ( $this->pattern === '*' || $this->pattern === $url ) {
            return true;
        }

        $ids       = array();
        $last_char = substr( $this->pattern, -1 );

        // Get splat
        if ( $last_char === '*' ) {
            $n     = 0;
            $len   = strlen( $url );
            $count = substr_count( $this->pattern, '/' );

            for ( $i = 0; $i < $len; $i++ ) {
                if ( $url[$i] == '/' )
                    $n++;
                if ( $n == $count )
                    break;
            }

            $this->splat = (string) substr( $url, $i + 1 );
        }

        // Build the regex for matching
        $regex = str_replace( array( ')', '/*' ), array( ')?', '(/?|/.*?)' ), $this->pattern );

        $regex = preg_replace_callback(
                '#@([\w]+)(:([^/\(\)]*))?#',
                function ( $matches ) use ( &$ids ) {
                    $ids[$matches[1]] = null;
                    if ( isset( $matches[3] ) ) {
                        return '(?P<' . $matches[1] . '>' . $matches[3] . ')';
                    }
                    return '(?P<' . $matches[1] . '>[^/\?]+)';
                }, $regex
        );

        // Fix trailing slash
        if ( $last_char === '/' ) {
            $regex .= '?';
        }
        // Allow trailing slash
        else {
            $regex .= '/?';
        }

        // Attempt to match route and named parameters
        if ( preg_match( '#^' . $regex . '(?:\?.*)?$#' . (($case_sensitive) ? '' : 'i'), $url, $matches ) ) {
            foreach ( $ids as $k => $v ) {
                $this->params[$k] = (array_key_exists( $k, $matches )) ? urldecode( $matches[$k] ) : null;
            }

            $this->regex = $regex;

            return true;
        }

        return false;
    }
}
