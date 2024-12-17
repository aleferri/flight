<?php

/*
 * The MIT License
 *
 * Copyright 2024 Alessio.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace flight\blocks;

/**
 * Description of ScopeRenderer
 *
 * @author Alessio
 */
class CommonRenderer implements Renderer {

    private $registry;
    private $stack;

    public function __construct(Registry $registry) {
        $this->registry = $registry;
        $this->stack = [];
    }

    private function current_scope() {
        if ( count( $this->stack ) === 0 ) {
            return 'root';
        }

        $last = count( $this->stack ) - 1;
        return $this->stack[ $last ][ 'scope' ];
    }

    private function lookup_block(string $key) {
        if ( strpos( $key, '/' ) === false ) {
            $key = $this->current_scope() . '/' . $key;
        }

        [ $scope, $name ] = explode( '/', $key );

        return $this->registry->lookup( $scope, $name );
    }

    public function render($b, array $args): string {
        if ( is_string( $b ) ) {
            $b = $this->lookup_block( $b );
        }

        if ( count( $this->stack ) === 0 ) {
            ob_start();
        }

        $this->push( $b->scope(), $args );

        include($b->location());

        $this->pop();

        if ( count( $this->stack ) === 0 ) {
            $content = ob_get_clean();

            return $content;
        }

        return $b->name();
    }

    public function get(string $key) {
        $len = count( $this->stack ) - 1;

        $args = $this->stack[ $len ][ 'args' ];

        if ( isset( $args[ $key ] ) ) {
            return $args[ $key ];
        }

        return null;
    }

    private function push(string $scope, array $args): void {
        $this->stack[] = [ 'scope' => $scope, 'args' => $args ];
    }

    private function pop() {
        return array_pop( $this->stack );
    }

}
