<?php

namespace Test;

use \PHPUnit\Framework\TestCase;
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Server\MiddlewareInterface;

class SetupClass extends TestCase
{
    protected static $container;

    public static function setUpBeforeClass(): void
    {
        self::$container = new \League\Container\Container();

        $middleware = new Class implements \Psr\Http\Server\MiddlewareInterface {
            public function process(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface
            {
                $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
                $responseBody = $psr17Factory->createStream('middleware');

                return $psr17Factory->createResponse(200)->withBody($responseBody);
            }
        };

        $controller = new Class {
            public function test(\Psr\Http\Message\ServerRequestInterface $request, array $args)
            {
                $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
                $responseBody = $psr17Factory->createStream('test');
                return $psr17Factory->createResponse(200)->withBody($responseBody);
            }
        };

        $after = new Class {
            public function doACall($request, $args, $response)
            {
                echo 'after';
            }
        };

        self::$container->add('middleware', $middleware);
        self::$container->add('controller', $controller);
        self::$container->add('after', $after);

        self::mockRequest('GET', '/admin');
    }

    public static function tearDownAfterClass(): void
    {
        self::$container = null;
    }

    public static function mockRequest($method, $path)
    {
        $server = $_SERVER;
        $server['REQUEST_METHOD'] = strtoupper($method);
        $server['REQUEST_URI'] = $path;

        return (new \Nyholm\Psr7\Factory\Psr17Factory())
            ->createServerRequest($method, $path, $server);
    }
}