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
 * Description of Registry
 *
 * @author Alessio
 */
class Registry {

    private $blocks;

    public function __construct() {
        $this->blocks = [];
    }

    /**
     * Register a new block
     * @param Block $b
     * @return void
     */
    public function register(Block $b): void {
        $key = $b->scope() . '/' . $b->name();
        $this->blocks[ $key ] = $b;
    }

    /**
     * Unregister the specified block
     * @param string $scope
     * @param string $name
     * @return void
     */
    public function unregister(string $scope, string $name): void {
        $key = $scope . '/' . $name;

        unset( $this->blocks[ $key ] );
    }

    /**
     * Lookup the block by it's context and it's name
     * @param string $scope
     * @param string $name
     * @return bool|Block
     */
    public function lookup(string $scope, string $name) {
        $key = $scope . '/' . $name;

        if ( ! isset( $this->blocks[ $key ] ) ) {
            return false;
        }

        return $this->blocks[ $key ];
    }

    /**
     *
     * @param string $scope
     * @param string $name
     * @return bool
     */
    public function exists(string $scope, string $name): bool {
        $key = $scope . '/' . $name;

        if ( ! isset( $this->blocks[ $key ] ) ) {
            return false;
        }

        return true;
    }

}
