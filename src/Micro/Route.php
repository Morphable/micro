<?php

namespace Morphable\Micro;

use \Morphable\Micro\Route\Pattern;
use \Psr\Http\Message\ServerRequestInterface;

class Route
{
    /** @var string */
    protected $method;

    /** @var \Morphable\Micro\Route\Pattern */
    protected $pattern;

    /** @var callable */
    protected $callback;

    /** @var array */
    protected $middleware;

    /**
     * construct
     *
     * @param string $method
     * @param string $pattern
     */
    public function __construct(string $method, string $pattern, $callback, array $middleware = [])
    {
        $this->method = strtoupper($method);
        $this->pattern = new Pattern($pattern);
        $this->callback = $callback;
        $this->middleware = $middleware;
    }

    /**
     * append middleware
     *
     * @param mixed $middleware
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
     * match a request
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public function match(ServerRequestInterface $request)
    {
        if ($this->method !== $request->getServerParams()['REQUEST_METHOD']) {
            return false;
        }

        $path = explode('?', $request->getServerParams()['REQUEST_URI']);
        $path = '/' . Pattern::normalize(reset($path));
        
        $regex = $this->pattern->getRegex();

        if (!preg_match($regex, $path)) {
            return false;
        }

        return true;
    }

    /**
     * get arguments from request
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    public function getArguments(ServerRequestInterface $request)
    {
        $parameters = Pattern::pathToParams($request->getServerParams()['REQUEST_URI']);

        $arguments = [];
        foreach ($this->pattern->getArguments() as $name => $index) {
            $arguments[$name] = $parameters[$index] ?? null;
        }

        return $arguments;
    }

    /**
     * get method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * get pattern
     *
     * @return \Morphable\Micro\Route\Pattern
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * get middleware
     *
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * get middleware
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }
}
