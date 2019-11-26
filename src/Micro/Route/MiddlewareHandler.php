<?php

namespace Morphable\Micro\Route;

use \Psr\Http\Server\RequestHandlerInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

/**
 * middleware handler
 */
class MiddlewareHandler implements RequestHandlerInterface
{
    /** @var array */
    protected $middleware = [];

    /** @var RequestHandlerInterface */
    protected $fallback;

    /**
     * construct
     *
     * @param array $middleware
     * @param RequestHandlerInterface $fallback
     */
    public function __construct(array $middleware, RequestHandlerInterface $fallback)
    {
        $this->middleware = $middleware;
        $this->fallback = $fallback;
    }

    /**
     * handler
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
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
