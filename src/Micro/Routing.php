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
        $routing = new self($prefix);
        $callback($routing);
        $this->groups[] = $routing;
        
        return $routing;
    }

    /**
     * add prefix to current route
     *
     * @param string $prefix
     * @return self
     */
    public function addPrefix($prefix)
    {
        $this->prefix = '/' . trim($prefix, '/') . '/' . trim($this->prefix, '/');
        return $this;
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
            $group->addPrefix($this->prefix);
            $group->addMiddleware($this->middleware);
            $result = array_merge($result, $group->getRoutes());
        }

        return $result;
    }
}
