<?php

namespace Morphable;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \Morphable\Micro\Route;
use \Morphable\Micro\Route\RouteDispatcher;

class Micro implements \Psr\Http\Server\RequestHandlerInterface
{
    protected $routes = [];

    protected $container;

    public function __construct()
    {
    }

    public function setContainer(\Psr\Container\ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }

    public function route(string $method, string $pattern, $callback, array $middleware = [])
    {
        $method = strtoupper($method);
        $route =new Route($method, $pattern, $callback, $middleware);
        $this->routes[$method][] = $route;

        return $route;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getServerParams()['REQUEST_METHOD'];
        
        $response = null;
        foreach ($this->routes[$method] as $route) {
            $dispatcher = new RouteDispatcher($route, $this->container);
            
            if ($route->match($request)) {
                $found = true;
                $response = $dispatcher->dispatch($request);
                break;
            }
        }

        if (empty($response)) {
            throw new \Exception("404");
        }

        return $response;
    }
}
