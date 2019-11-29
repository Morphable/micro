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

    protected $after = [];

    /**
     * construct
     *
     * @param string $prefix
     * @param array $middleware
     */
    public function __construct($prefix = '/', $middleware = [], $after = [])
    {
        $this->prefix = $prefix;
        $this->middleware = $middleware;
        $this->after = $after;
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
    public function add(string $method, string $pattern, $callback, array $middleware = [])
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
    public function prefix($prefix)
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
    public function middleware($callback)
    {
        $this->middleware[] = $callback;

        return $this;
    }

    public function after($callback)
    {
        $this->after[] = $callback;

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
            $route->getPattern()->prefix($this->prefix);

            foreach ($this->middleware as $callback) {
                $route->middleware($callback);
            }

            foreach ($this->after as $callback) {
                $route->after($callback);
            }

            $result[] = $route;
        }

        // add groups
        foreach ($this->groups as $group) {
            $group->prefix($this->prefix);

            foreach ($this->middleware as $callback) {
                $group->middleware($callback);
            }

            foreach ($this->after as $callback) {
                $group->after($callback);
            }

            $result = array_merge($result, $group->getRoutes());
        }

        return $result;
    }
}
