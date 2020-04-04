<?php

namespace App;

class Application
{
    private $_container;
    
    public function __construct($container)
    {
        $this->_container = $container;
    }
    
//    public function run()
//    {
//        $server = new \Laminas\HttpHandlerRunner\RequestHandlerRunner(
//            $this->_container->get('pipeline'),
//            new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter(),
//            static function () {
//                return \Laminas\Diactoros\ServerRequestFactory::fromGlobals();
//            }, 
//            static function (\Throwable $e) {
//                if ((bool)getenv('DEBUG') == true) {
//                    throw $e;
//                }
//                $response = (new \Laminas\Diactoros\ResponseFactory())->createResponse(500);
//                $response->getBody()->write($this->_container->get(\League\Plates\Engine::class)->render('app/error', ['e' => $e]));
//                return $response;
//            }
//        );
//
//        $server->run();
//    }
    
    public function run()
    {
        $container = $this->_container;
        $router = $container->get('router');
        $request = \Laminas\Diactoros\ServerRequestFactory::fromGlobals();
        try {
            $response = $router->dispatch($request);
        } catch (\League\Route\Http\Exception\NotFoundException $e) {
            $response = new \Laminas\Diactoros\Response();
            $response->getBody()->write($container->get(\League\Plates\Engine::class)->render('app/404', ['e' => $e]));
            $response->withStatus(404);
        } catch (\Exception $e) {
            if ((bool)getenv('DEBUG') == true) {
                throw $e;
            }
            $response = new \Laminas\Diactoros\Response();
            $response->getBody()->write($container->get(\League\Plates\Engine::class)->render('app/error', ['e' => $e]));
            $response->withStatus(500);
        }

        (new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);
    }
}
