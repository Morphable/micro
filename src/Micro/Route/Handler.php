<?php

namespace Morphable\Micro\Route;

use \Morphable\Micro\Route;
use \Psr\Http\Server\RequestHandlerInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class Handler implements RequestHandlerInterface
{
    protected $route;

    protected $middleware = [];

    protected $fallback;

    public function __construct(array $middleware, RequestHandlerInterface $fallback)
    {
        $this->middleware = $middleware;
        $this->fallback = $fallback;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (empty($this->middleware)) {
            return $this->fallback->handle($request);
        }
        
        $middleware = array_shift($this->middleware);

        if (method_exists($middleware, 'process')) {
            return $middleware->process($request, $this);
        }

        if (is_callable($middleware)) {
            return $middleware($request, $this);
        }

        return \call_user_func($middleware, $request, $this);
    }
}
