<?php

/**
 * Flight: An extensible micro-framework.
 *
 * @copyright   Copyright (c) 2012, Mike Cao <mike@mikecao.com>
 * @license     MIT, http://flightphp.com/license
 */

use flight\http\Request;

class RequestTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var \flight\net\Request
     */
    private $request;

    function setUp(): void {
        $_SERVER['REQUEST_URI']           = '/';
        $_SERVER['SCRIPT_NAME']           = '/index.php';
        $_SERVER['REQUEST_METHOD']        = 'GET';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_SERVER['REMOTE_ADDR']           = '8.8.8.8';
        $_SERVER['HTTP_X_FORWARDED_FOR']  = '32.32.32.32';
        $_SERVER['HTTP_HOST']             = 'example.com';
        
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        $_FILES = [];

        $this->request = new Request();
    }

    function testDefaults() {
        $this->assertEquals( '/', $this->request->url );
        $this->assertEquals( '/', $this->request->base );
        $this->assertEquals( 'GET', $this->request->method );
        $this->assertEquals( '', $this->request->referrer );
        $this->assertEquals( true, $this->request->ajax );
        $this->assertEquals( 'http', $this->request->scheme );
        $this->assertEquals( '', $this->request->type );
        $this->assertEquals( 0, $this->request->length );
        $this->assertEquals( false, $this->request->secure );
        $this->assertEquals( '', $this->request->accept );
        $this->assertEquals( 'example.com', $this->request->host );
    }

    function testIpAddress() {
        $this->assertEquals( '8.8.8.8', $this->request->ip );
        $this->assertEquals( '32.32.32.32', $this->request->proxy_ip );
    }

    function testSubdirectory() {
        $_SERVER['SCRIPT_NAME'] = '/subdir/index.php';

        $request = new Request();

        $this->assertEquals( '/subdir', $request->base );
    }

    function testQueryParameters() {
        $_SERVER['REQUEST_URI'] = '/page?id=1&name=bob';

        $request = new Request();

        $this->assertEquals( '/page?id=1&name=bob', $request->url );
        $this->assertEquals( 1, $request->query->id );
        $this->assertEquals( 'bob', $request->query->name );
    }

    function testCollections() {
        $_SERVER['REQUEST_URI'] = '/page?id=1';

        $_GET['q']    = 1;
        $_POST['q']   = 1;
        $_COOKIE['q'] = 1;
        $_FILES['q']  = 1;

        $request = new Request();

        $this->assertEquals( 1, $request->query->q );
        $this->assertEquals( 1, $request->query->id );
        $this->assertEquals( 1, $request->data->q );
        $this->assertEquals( 1, $request->cookies->q );
        $this->assertEquals( 1, $request->files->q );
    }

    function testMethodOverrideWithHeader() {
        $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'PUT';

        $request = new Request();

        $this->assertEquals( 'PUT', $request->method );
    }

    function testMethodOverrideWithPost() {
        $_REQUEST['_method'] = 'PUT';

        $request = new Request();

        $this->assertEquals( 'PUT', $request->method );
    }

    public function testJsonWithEmptyBody() {
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        
        $request                 = new Request();
        self::assertSame( [], $request->data->getData() );
    }

    function testHttps() {
        $_SERVER['HTTPS'] = 'on';
        $request          = new Request();
        $this->assertEquals( 'https', $request->scheme );
        $_SERVER['HTTPS'] = 'off';
        $request          = new Request();
        $this->assertEquals( 'http', $request->scheme );

        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $request                           = new Request();
        $this->assertEquals( 'https', $request->scheme );
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'http';
        $request                           = new Request();
        $this->assertEquals( 'http', $request->scheme );

        $_SERVER['HTTP_FRONT_END_HTTPS'] = 'on';
        $request                         = new Request();
        $this->assertEquals( 'https', $request->scheme );
        $_SERVER['HTTP_FRONT_END_HTTPS'] = 'off';
        $request                         = new Request();
        $this->assertEquals( 'http', $request->scheme );

        $_SERVER['REQUEST_SCHEME'] = 'https';
        $request                   = new Request();
        $this->assertEquals( 'https', $request->scheme );
        $_SERVER['REQUEST_SCHEME'] = 'http';
        $request                   = new Request();
        $this->assertEquals( 'http', $request->scheme );
    }
}
