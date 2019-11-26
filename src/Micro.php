<?php

namespace Morphable;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \Morphable\Micro\Route;
use \Morphable\Micro\Route\RouteDispatcher;
use \Morphable\Micro\Routing;
use \Psr\Container\ContainerInterface;

class Micro implements \Psr\Http\Server\RequestHandlerInterface
{
    /** @var Routing */
    protected $routing;

    /** @var ContainerInterface */
    protected $container;

    /**
     * get routing provider
     *
     * @return Routing
     */
    public function routing()
    {
        if (empty($this->routing)) {
            $this->routing = new Routing();
        }

        return $this->routing;
    }

    /**
     * set psr container
     *
     * @param \Psr\Container\ContainerInterface $container
     * @return self
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * handle routes
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = null;
        foreach ($this->routing()->getRoutes() as $route) {
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
