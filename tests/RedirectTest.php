<?php

/**
 * Flight: An extensible micro-framework.
 *
 * @copyright   Copyright (c) 2013, Mike Cao <mike@mikecao.com>
 * @license     MIT, http://flightphp.com/license
 */

class RedirectTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var \flight\Engine
     */
    private $app;

    function getBaseUrl( $base, $url ) {
        if ( $base != '/' && strpos( $url, '://' ) === false ) {
            $url = preg_replace( '#/+#', '/', $base . '/' . $url );
        }

        return $url;
    }

    function setUp(): void {
        $_SERVER['SCRIPT_NAME'] = '/subdir/index.php';

        $this->app = new \flight\Engine();
        $this->app->set( 'flight.base_url', '/testdir' );
    }

    // The base should be the subdirectory
    function testBase() {
        $base = $this->app->request()->base;

        $this->assertEquals( '/subdir', $base );
    }

    // Absolute URLs should include the base
    function testAbsoluteUrl() {
        $url  = '/login';
        $base = $this->app->request()->base;

        $this->assertEquals( '/subdir/login', $this->getBaseUrl( $base, $url ) );
    }

    // Relative URLs should include the base
    function testRelativeUrl() {
        $url  = 'login';
        $base = $this->app->request()->base;

        $this->assertEquals( '/subdir/login', $this->getBaseUrl( $base, $url ) );
    }

    // External URLs should ignore the base
    function testHttpUrl() {
        $url  = 'http://www.yahoo.com';
        $base = $this->app->request()->base;

        $this->assertEquals( 'http://www.yahoo.com', $this->getBaseUrl( $base, $url ) );
    }

    // Configuration should override derived value
    function testBaseOverride() {
        $url = 'login';
        if ( $this->app->get( 'flight.base_url' ) !== null ) {
            $base = $this->app->get( 'flight.base_url' );
        } else {
            $base = $this->app->request()->base;
        }

        $this->assertEquals( '/testdir/login', $this->getBaseUrl( $base, $url ) );
    }
}
