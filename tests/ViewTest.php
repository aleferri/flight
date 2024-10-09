<?php

/**
 * Flight: An extensible micro-framework.
 *
 * @copyright   Copyright (c) 2012, Mike Cao <mike@mikecao.com>
 * @license     MIT, http://flightphp.com/license
 */
use flight\blocks\SimpleBlock;
use flight\blocks\Registry;
use flight\blocks\CommonRenderer;

class ViewTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var \flight\template\View
     */
    private $view;

    function setUp(): void {
        $this->view = new \flight\template\View();
        $this->view->path = __DIR__ . '/views';
    }

    // Set template variables
    function testVariables() {
        $this->view->set( 'test', 123 );

        $this->assertEquals( 123, $this->view->get( 'test' ) );

        $this->assertTrue( $this->view->has( 'test' ) );
        $this->assertTrue(  ! $this->view->has( 'unknown' ) );

        $this->view->clear( 'test' );

        $this->assertEquals( null, $this->view->get( 'test' ) );
    }

    // Check if template files exist
    function testTemplateExists() {
        $this->assertTrue( $this->view->exists( 'hello.php' ) );
        $this->assertTrue(  ! $this->view->exists( 'unknown.php' ) );
    }

    // Render a template
    function testRender() {
        $this->view->render( 'hello', array( 'name' => 'Bob' ) );

        $this->expectOutputString( 'Hello, Bob!' );
    }

    // Fetch template output
    function testFetch() {
        $output = $this->view->fetch( 'hello', array( 'name' => 'Bob' ) );

        $this->assertEquals( 'Hello, Bob!', $output );
    }

    // Default extension
    function testTemplateWithExtension() {
        $this->view->set( 'name', 'Bob' );

        $this->view->render( 'hello.php' );

        $this->expectOutputString( 'Hello, Bob!' );
    }

    // Custom extension
    function testTemplateWithCustomExtension() {
        $this->view->set( 'name', 'Bob' );
        $this->view->extension = '.html';

        $this->view->render( 'world' );

        $this->expectOutputString( 'Hello world, Bob!' );
    }

    function testBlocksRegistry() {
        $main = new SimpleBlock( __DIR__ . '/blocks/main.php', 'main', 'root', [ 'title' ] );
        $content = new SimpleBlock( __DIR__ . '/blocks/content.php', 'content', 'root', [ 'text' ] );

        $registry = new Registry();
        $registry->register( $main );
        $registry->register( $content );

        $this->assertTrue( $registry->lookup( 'root', 'main' ) instanceof SimpleBlock );
        $this->assertEquals( false, $registry->lookup( 'none', 'none' ) );

        $this->assertTrue( $registry->exists( 'root', 'main' ) );
        $this->assertFalse( $registry->exists( 'none', 'main' ) );
    }

    function testRenderBlocks() {
        $main = new SimpleBlock( __DIR__ . '/blocks/main.php', 'main', 'root', [ 'title' ] );
        $content = new SimpleBlock( __DIR__ . '/blocks/content.php', 'content', 'root', [ 'text' ] );

        $registry = new Registry();
        $registry->register( $main );
        $registry->register( $content );

        $renderer = new CommonRenderer( $registry );
        $text = $renderer->render( $main, [ 'title' => 'example' ] );

        $processed = str_replace( [ "\n", "    " ], '', trim( $text ) );

        $this->assertEquals( "<!DOCTYPE><html><head><title>example</title></head><body><h1>hello world</h1></body></html>", $processed );
    }

}
