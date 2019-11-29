# Micro
a micro framework based on the psr-15 and psr-7 standard

### Simple usage
```php
<?php

use \Morphable\Micro;
use \Psr\Http\Message\ServerRequestInterface;

$micro = new Micro();

$router = $micro->routing();

$router->add('GET', '/user/:id', function (ServerRequestInterface $request, array $args) {
    $id = $args['id'];

    /** @link https://www.php-fig.org/psr/psr-7/ */
    return $response;
});



try {
    $response = $micro->handle($request); // \Psr\Http\Message\ResponseInterface
} catch (\Exception $e) {
    // 404
}
```

### Callbacks
```php
<?php

use \Psr\Http\Message\ServerRequestInterface;

// specific method in controller
$router->add('GET', '/user/:id', ['controller', 'method']);

// __invoke
$router->add('GET', '/user/:id', 'controller');

// static method
$router->add('GET', '/user/:id', [controller::class, 'method']);

function callback(ServerRequestInterface $request, array $args) {
    return $response;
}

// function
$router->add('GET', '/user/:id', 'callback');

// annonymous function
$router->add('GET', '/user/:id', function (ServerRequestInterface $request, array $args) {
    return $response;
});
```

### Route pattern
```php
<?php

// : is mandatory argument ?: is optional argument
$router->add('GET', '/user/:userId/profile', function ($request, $args){
    $args['userId'] // second parameter in url
});

$router->add('GET', '/callback/?:channel', function ($request, $args) {
    $args['channel'] // second parameter or null
})
```

### Middleware
```php
<?php

use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Server\MiddlewareInterface;

/** @link https://www.php-fig.org/psr/psr-15/ */
class Middleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // return ResponseInterface or handle next request
        return $handler->handle($request);
    }
}

$router->add('GET', '/user/:id', ['controller', 'method'])
    ->middleware('middleware'); // from container, can be string or array
```

### Groups
```php
<?php

$router->group('api', function ($router) { // prefix of api

    $router->add('GET', '/', ['controller', 'method']); // pattern: /api

    $router->group('user', function ($router) {
        $router->add('GET', '/:id', ['controller', 'method']); // pattern: /api/user/:id
    })->middleware(['middleware']);

})->middleware('middleware'); // counts for every route inside

```
