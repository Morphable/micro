<?php

use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Container\ContainerInterface;
use \Morphable\Micro\Route;
use \Psr\Http\Server\RequestHandlerInterface;
use \Morphable\Micro\Route\Dispatcher;


require __DIR__ . '/vendor/autoload.php';

class A implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        echo "middleware\n";
        return $handler->handle($request);
    }
}

class SomeDependency
{
    public function idkWhatImDoing()
    {
        // return psr-7 response interface
        $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
        $responseBody = $psr17Factory->createStream('Hello world');
        return $psr17Factory->createResponse(200)->withBody($responseBody);
    }
}

class BController {
    
    protected $idk;

    public function __construct($idkWhatImDoing)
    {
        $this->idk = $idkWhatImDoing;
    }

    public function someAction($request, $args) {
        return $this->idk->idkWhatImDoing();
    }

    public function __invoke($request, $args)
    {
        return $this->idk->idkWhatImDoing();
    }
}

// create request
$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
$creator = new \Nyholm\Psr7Server\ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);
$request = $creator->fromGlobals();

$container = new \League\Container\Container();
$container->add('middleware', A::class);
$container->add('idk', SomeDependency::class);
$container->add('controller', BController::class)->addArgument('idk');

$micro = new \Morphable\Micro();
$micro->setContainer($container);

$router = $micro->routing();

$router->add('GET', '/', ['controller', 'someAction'])->middleware(['middleware']);

$router->group('api', function ($router) {
    
    $router->group('user', function ($router) {
        
        $router->add('GET', '/', function () {
            die('user index');
        });

        $router->add('GET', '/edit', function () {
            die('edit');
        });

        $router->add('GET', '/:id', function ($req, $args) {
            die($args['id']);
        })->middleware('middleware');
     });
})->middleware('middleware');


$router->add('get', '/channel/callback/?:method', function ($req, $args) {
    echo '<pre>';
    print_r($args);
    echo '</pre>';
    die;
})->match($request);

try {
    $response = $micro->handle($request);
} catch (\Exception $e) {
    die('404');
}

echo '<pre>';
print_r((string) $response->getBody());
echo '</pre>';
die;
