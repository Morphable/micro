<?php

namespace tests;

use \Morphable\Micro\Route;
use \Symfony\Component\HttpFoundation\Request;

class RouteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * show case of the api
     */
    private function api()
    {
        $route = new Route('GET', '/home');

        $request = Request::createFromGlobals();

        // GET /home: 
        // POST /home: 
        $route->match($request);

    }
}
