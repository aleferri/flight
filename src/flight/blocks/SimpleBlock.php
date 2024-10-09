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
 * Description of SimpleBlock
 *
 * @author Alessio
 */
class SimpleBlock implements Block {

    private $location;
    private $name;
    private $scope;
    private $args;

    public function __construct(string $location, string $name, string $scope = 'root', array $args = []) {
        $this->location = $location;
        $this->name = $name;
        $this->scope = $scope;
        $this->args = $args;
    }

    public function scope(): string {
        return $this->scope;
    }

    public function name(): string {
        return $this->name;
    }

    public function location(): string {
        return $this->location;
    }

    public function args(): array {
        return $this->args;
    }

}
