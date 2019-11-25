<?php

namespace tests;

use \Morphable\Micro;
use \Morphable\Micro\Route;
use \Morphable\Micro\Route\Dispatcher;

class DispatcherTests extends \PHPUnit\Framework\TestCase
{
    /**
     * show case of the api
     */
    private function api()
    {
        $route = (new Route('GET', '/home', function ($request, $args) {
            return 'Response';
        }))->addMiddleware(['psr7 middleware', 'from', 'container']);

        $dispatcher = new Dispatcher($route, $container);
        
        try {
            $response = $dispatcher->dispatch($request);
        } catch (\Exception $e) {
        }
    }
}
