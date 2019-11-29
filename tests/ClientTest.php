<?php

namespace Test;

use \Morphable\Micro;
use \Psr\Http\Message\ResponseInterface;

class ClientTest extends SetupClass
{
    public function testClient()
    {
        $client = new Micro();
        $client->setContainer(self::$container);

        $router = $client->routing();

        $router->add('GET', '/test', ['controller', 'test']);

        $request = self::mockRequest('GET', '/test');

        try {
            $response = $client->handle($request);
        } catch (\Exception $e) {
        }

        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $response);
    }

    public function testGroup()
    {
        $client = new Micro();
        $client->setContainer(self::$container);

        $router = $client->routing();

        $router->group('test', function ($router) {
           $router->add('post', '/123', ['controller', 'test']);
        });

        $request = self::mockRequest('POST', '/test/123');

        try {
            $response = $client->handle($request);
        } catch (\Exception $e) {
        }

        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $response);
    }

    public function testMiddleware()
    {
        $client = new Micro();
        $client->setContainer(self::$container);

        $router = $client->routing();

        $router->add('GET', '/test', ['controller', 'test'])->middleware('middleware');

        $request = self::mockRequest('GET', '/test');
        
        try {
            $response = $client->handle($request);
        } catch (\Exception $e) {
        }

        $this->assertSame((string) $response->getBody(), 'middleware');
    }

    public function testAfter()
    {
        $client = new Micro();
        $client->setContainer(self::$container);

        $router = $client->routing();

        $router->add('GET', '/test', ['controller', 'test'])
            ->middleware('middleware')
            ->middleware('middleware')
            ->after(['after', 'doACall']);

        $request = self::mockRequest('GET', '/test');
        
        ob_start();

        try {
            $response = $client->handle($request);
        } catch (\Exception $e) {
        }

        $s = \ob_get_clean();

        $this->assertSame($s, 'after');
    }
}
