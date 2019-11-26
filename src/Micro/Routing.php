<?php

namespace Morphable\Micro;

class Routing
{
    /** @var array */
    protected $routes = [];

    /** @var array */
    protected $groups = [];

    /** @var string */
    protected $prefix = '/';

    /** @var array */
    protected $middleware = [];

    /**
     * construct
     *
     * @param string $prefix
     * @param array $middleware
     */
    public function __construct($prefix = '/', $middleware = [])
    {
        $this->prefix = $prefix;
        $this->middleware = $middleware;
    }

    /**
     * add route
     *
     * @param string $method
     * @param string $pattern
     * @param callable|string $callback
     * @param array $middleware
     * @return Route
     */
    public function route(string $method, string $pattern, $callback, array $middleware = [])
    {
        $method = strtoupper($method);
        $route = new Route($method, $pattern, $callback, $middleware);
        $this->routes[] = $route;

        return $route;
    }

    /**
     * add group
     *
     * @param string $prefix
     * @param callable $callback
     * @return Routing
     */
    public function group($prefix, $callback)
    {
        $prefix = '/' . trim($this->prefix, '/') . '/' . trim($prefix, '/');

        $routing = new self($prefix, $this->middleware);
        $this->groups[] = $routing;
        $callback($routing);
        return $routing;
    }

    /**
     * add middleware to all routes
     *
     * @param array|string $middleware
     * @return self
     */
    public function addMiddleware($middleware)
    {
        if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
            return $this;
        }

        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * get routes
     *
     * @return array
     */
    public function getRoutes()
    {
        $result = [];
        foreach ($this->routes as $route) {
            // build routes
            $route->addMiddleware($this->middleware);
            $route->getPattern()->addPrefix($this->prefix);
            $result[] = $route;
        }

        // add groups
        foreach ($this->groups as $group) {
            $result = array_merge($result, $group->getRoutes());
        }

        return $result;
    }
}
