<?php

namespace Morphable\Micro\Route;

use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Container\ContainerInterface;
use \Morphable\Micro\Route;
use \Psr\Http\Server\RequestHandlerInterface;

class Dispatcher implements RequestHandlerInterface
{
    /** @var \Symfony\Component\HttpFoundation\Request */
    protected $request;

    /** @var \Symfony\Component\HttpFoundation\Response */
    protected $response;

    /** @var \Psr\Container\ContainerInterface */
    protected $container;

    /**
     * construct
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct(Route $route, ContainerInterface $container = null)
    {
        $this->route = $route;
        $this->container = $container;
    }

    /**
     * final handler
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $callback = $this->route->getCallback();

        $args = [
            $request,
            new \Nyholm\Psr7\Factory\Psr17Factory(),
            $this->route->getArguments($request)
        ];

        if ($this->container instanceof \Psr\Container\ContainerInterface) {
            // specific method
            if (is_array($callback) && $this->container->has(reset($callback))) {
                $containerItem = $this->container->get(reset($callback));
                
                if (method_exists($containerItem, end($callback))) {
                    return $containerItem->{end($callback)}(...$args);
                }
            }
        }

        return \call_user_func($callback, ...$args);
    }

    /**
     * match route
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function match(ServerRequestInterface $request)
    {
        return $this->route->match($request);
    }

    /**
     * dispatch route with middleware
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        return (new Handler($this->route->getMiddleware(), $this))->handle($request);
    }
}
