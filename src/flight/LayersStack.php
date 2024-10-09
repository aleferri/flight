<?php

namespace flight;

/*
 * LayersStack is a push down collection of middleware layers, at resolution they are resolved at inverted order
 */

class LayersStack {

    //Stack of layers
    private $layers;
    private $engine;

    /**
     * Public constructor of the LayersStack
     * @param bool $push_self push the realDispatch method as the final layer of the middlewares
     */
    public function __construct(Engine $engine, $push_self = false) {
        $this->engine = $engine;
        $this->layers = [];
        if ( $push_self ) {
            $this->layers[] = [ $this, 'realDispatch' ];
        }
    }

    /**
     * Push callable into the middleware stack
     */
    public function push(callable $layer) {
        $this->layers[] = $layer;
    }

    /**
     * Dispatch is a repeatable middlewares dispatch function that start the iteration on the current stack of layers until there are no more
     *
     * You shouldn't invoke it directly, instead map this method to Flight as "dispatchRoute" with Flight::map( 'dispatchRoute', [ $thisObject, 'dispatch' ]
     * overriding the original method and let Flight call it for you when it find a compatible route for the request.
     * As the router may found multiple routes compatibile with yours the repeatability of the dispatch function is a must
     */
    public function dispatch(http\Route $route, array $params = []): http\Response {
        $iterator = new dispatch\DispatchIterator( $this->layers );
        return $iterator->next( $route, $this->engine->request(), $params );
    }

    /**
     * RealDispatch is a super thin wrapper over the original Flight::dispatchRoute
     * @param http\Route $route
     * @param http\Request $request
     * @param array $params
     * @param dispatch\DispatchIterator $iterator
     * @return type
     */
    public function realDispatch(http\Route $route, http\Request $request, array $params, dispatch\DispatchIterator $iterator) {
        $params = array_merge( [ $request ], $params );

        return $this->engine->_dispatchRoute( $route, $params );
    }

}
