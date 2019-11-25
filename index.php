<?php

use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Container\ContainerInterface;
use \Morphable\Micro\Route;
use \Psr\Http\Server\RequestHandlerInterface;
use \Morphable\Micro\Route\Dispatcher;

require __DIR__ . '/vendor/autoload.php';



$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();

$creator = new \Nyholm\Psr7Server\ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);

$request = $creator->fromGlobals();

$route = new \Morphable\Micro\Route('GET', '/user/:userId', function ($request, $factory, $args) {
    return $factory->createResponse(200)->withBody($factory->createStream('Hello world'));
});

$middleware = new Class implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        echo 'processing';
        return $handler->handle($request);
    }
};

$route->addMiddleware([$middleware]);

$dispatcher = new Dispatcher($route, null);
if ($dispatcher->match($request)) {
    $response = $dispatcher->dispatch($request);
}