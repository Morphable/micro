<?php

namespace tests;

use \Morphable\Micro;
use \Morphable\Micro\Route;
use \Symfony\Component\HttpFoundation\Request;

class RoutingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * show case of the api
     */
    private function api()
    {
        $micro = new Micro();

        $routing = $micro->routing();

        $routing->group('/api', function ($routing) {
            $routing->route('GET', '/user/:id', function ($req, $args) {
                die($args['id']);
            });
        })->addMiddleware('auth.basic');

        $routing->route('GET', '/', function ($req, $args) {
            die('home');
        });

        $micro->handle($request);
    }
}
