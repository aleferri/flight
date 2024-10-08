<?php

/**
 * Flight: An extensible micro-framework.
 *
 * @copyright   Copyright (c) 2012, Mike Cao <mike@mikecao.com>
 * @license     MIT, http://flightphp.com/license
 */

class RenderTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var \flight\Engine
     */
    private $app;

    function setUp(): void {
        $this->app = new \flight\Engine();
        $this->app->set( 'flight.views.path', __DIR__ . '/views' );
    }

    // Render a view
    function testRenderView() {
        $this->app->render( 'hello', array( 'name' => 'Bob' ) );

        $this->expectOutputString( 'Hello, Bob!' );
    }

    // Renders a view into a layout
    function testRenderLayout() {
        $this->app->render( 'hello', array( 'name' => 'Bob' ), 'content' );
        $this->app->render( 'layouts/layout' );

        $this->expectOutputString( '<html>Hello, Bob!</html>' );
    }
}
