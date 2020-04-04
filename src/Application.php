<?php

namespace App;

class Application
{
    private $_container;
    
    public function __construct($container)
    {
        $this->_container = $container;
    }
    
    public function run()
    {
        $server = new \Laminas\HttpHandlerRunner\RequestHandlerRunner(
            $this->_container->get('pipeline'),
            new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter(),
            static function () {
                return \Laminas\Diactoros\ServerRequestFactory::fromGlobals();
            }, 
            static function (\Throwable $e) {
                if ((bool)getenv('DEBUG') == true) {
                    throw $e;
                }
                $response = (new \Laminas\Diactoros\ResponseFactory())->createResponse(500);
                $response->getBody()->write($this->_container->get(\League\Plates\Engine::class)->render('app/error', ['e' => $e]));
                return $response;
            }
        );

        $server->run();
    }
}
