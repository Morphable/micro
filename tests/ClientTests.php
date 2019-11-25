<?php

namespace tests;

use \Morphable\Micro;

class ClientTests extends \PHPUnit\Framework\TestCase
{
    /**
     * show case of the api
     */
    private function api()
    {
        $client = new \Morphable\Micro();
        $client->setContainer($container);

        $client
            ->route('GET', '/home', $callback)
            ->addMiddleware(['psr7 middleware', 'from', 'container']);

        

        $response = $client->handle($request); // \Psr\Http\Message\ResponseInterface
    }
}
