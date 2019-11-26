<?php

namespace Morphable\Micro\Route;

use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Container\ContainerInterface;
use \Morphable\Micro\Route;
use \Morphable\Micro\Route\MiddlewareHandler;
use \Psr\Http\Server\RequestHandlerInterface;

/**
 * route dispatcher
 */
class RouteDispatcher implements RequestHandlerInterface
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
     * get middleware from container 
     *
     * @param array $middleware
     * @return array
     */
    public function populateMiddleware($middleware)
    {
        $result = [];

        foreach ($middleware as $callback) {
            if ($this->container instanceof \Psr\Container\ContainerInterface) {
                if (is_string($callback) && $this->container->has($callback)) {
                    $result[] = $this->container->get($callback);
                    continue;
                }
            }

            $result[] = $callback;
        }

        return $result;
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

        $args = [ $request, $this->route->getArguments($request) ];

        if ($this->container instanceof \Psr\Container\ContainerInterface) {
            // specific method
            if (is_array($callback) && $this->container->has(reset($callback))) {
                $containerItem = $this->container->get(reset($callback));
                
                if (method_exists($containerItem, end($callback))) {
                    return $containerItem->{end($callback)}(...$args);
                }
            // __invoke
            } elseif (is_string($callback) && $this->container->has($callback)) {
                $callback = $this->container->get($callback);
            }
        }

        return \call_user_func($callback, ...$args);
    }

    /**
     * dispatch route with middleware
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        return (new MiddlewareHandler($this->populateMiddleware($this->route->getMiddleware()), $this))->handle($request);
    }
}
