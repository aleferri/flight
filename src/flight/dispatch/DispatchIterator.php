<?php

namespace flight\dispatch;

use flight\http\Route;
use flight\http\Request;

/**
 * LayersIterator iterate over the middlewares stack and call the next layer until it abort or reach the final layer (the route layer)
 */
class DispatchIterator {

    /**
     * Middleware layers
     * @var array
     */
    private $layers;

    /**
     * Layers cursor
     * @var int
     */
    private $cursor;

    /**
     * @param array $layers stack of middleware layers
     */
    public function __construct(array $layers = []) {
        $this->layers = $layers;
        $this->cursor = count( $layers ) - 1;
    }

    /**
     * Dispatch the next layer of the stack
     * @param Route $route route found by the router
     * @param Request $request Flight request object
     * @param array $params list of params for the route
     * @return void
     */
    public function next(Route $route, Request $request, array $params) {
        if ( $this->cursor > -1 ) {
            $callable = $this->layers[ $this->cursor ];
            $this->cursor --;
            return $callable( $route, $request, $params, $this );
        }
    }

}
