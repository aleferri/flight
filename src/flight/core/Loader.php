<?php
/**
 * Flight: An extensible micro-framework.
 *
 * @copyright   Copyright (c) 2011, Mike Cao <mike@mikecao.com>
 * @license     MIT, http://flightphp.com/license
 */

namespace flight\core;

/**
 * The Loader class is responsible for loading objects. It maintains
 * a list of reusable class instances and can generate a new class
 * instances with custom initialization parameters. It also performs
 * class autoloading.
 */
class Loader {
    /**
     * Registered classes.
     *
     * @var array
     */
    protected $classes = array();

    /**
     * Class instances.
     *
     * @var array
     */
    protected $instances = array();

    /**
     * Registers a class.
     *
     * @param string $name Registry name
     * @param string|callable $class Class name or function to instantiate class
     * @param array $params Class initialization parameters
     * @param callback $callback Function to call after object instantiation
     */
    public function register($name, $class, array $params = array(), $callback = null) {
        unset($this->instances[$name]);

        $this->classes[$name] = array($class, $params, $callback);
    }

    /**
     * Unregisters a class.
     *
     * @param string $name Registry name
     */
    public function unregister($name) {
        unset($this->classes[$name]);
    }

    /**
     * Loads a registered class.
     *
     * @param string $name Method name
     * @param bool $shared Shared instance
     * @return object Class instance
     * @throws \Exception
     */
    public function load($name, $shared = true) {
        $obj = null;

        if (isset($this->classes[$name])) {
            list($class, $params, $callback) = $this->classes[$name];

            $exists = isset($this->instances[$name]);

            if ($shared) {
                $obj = ($exists) ?
                    $this->getInstance($name) :
                    $this->newInstance($class, $params);
                
                if (!$exists) {
                    $this->instances[$name] = $obj;
                }
            }
            else {
                $obj = $this->newInstance($class, $params);
            }

            if ($callback && (!$shared || !$exists)) {
                $ref = array(&$obj);
                call_user_func_array($callback, $ref);
            }
        }

        return $obj;
    }

    /**
     * Gets a single instance of a class.
     *
     * @param string $name Instance name
     * @return object Class instance
     */
    public function getInstance($name) {
        return isset($this->instances[$name]) ? $this->instances[$name] : null;
    }

    /**
     * Gets a new instance of a class.
     *
     * @param string|callable $class Class name or callback function to instantiate class
     * @param array $params Class initialization parameters
     * @return object Class instance
     * @throws \Exception
     */
    public function newInstance($class, array $params = array()) {
        if (is_callable($class)) {
            return call_user_func_array($class, $params);
        }

        switch (count($params)) {
            case 0:
                return new $class();
            case 1:
                return new $class($params[0]);
            case 2:
                return new $class($params[0], $params[1]);
            case 3:
                return new $class($params[0], $params[1], $params[2]);
            case 4:
                return new $class($params[0], $params[1], $params[2], $params[3]);
            case 5:
                return new $class($params[0], $params[1], $params[2], $params[3], $params[4]);
            default:
                try {
                    $refClass = new \ReflectionClass($class);
                    return $refClass->newInstanceArgs($params);
                } catch (\ReflectionException $e) {
                    throw new \Exception("Cannot instantiate {$class}", 0, $e);
                }
        }
    }

    /**
     * @param string $name Registry name
     * @return mixed Class information or null if not registered
     */
    public function get($name) {
        return isset($this->classes[$name]) ? $this->classes[$name] : null;
    }

    /**
     * Resets the object to the initial state.
     */
    public function reset() {
        $this->classes = array();
        $this->instances = array();
    }

}
