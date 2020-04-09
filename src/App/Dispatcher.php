<?php declare(strict_types=1);

namespace App;

use FastRoute\Dispatcher as FastRoute;
use League\Route\Http\Exception\{MethodNotAllowedException, NotFoundException};
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

class Dispatcher extends \League\Route\Dispatcher
{

    public function match(ServerRequestInterface $request)
    {
        $httpMethod = $request->getMethod();
        $uri = $request->getUri()->getPath();
        return $this->dispatch($httpMethod, $uri);
    }
    
    /**
     * Dispatch the current route
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function dispatchRequest(ServerRequestInterface $request): ResponseInterface
    {
        $match = $this->match($request);
        $routeResult = new RouteResult($match);
        if ($match[0] === FastRoute::FOUND) {
            $routeResult->setFound($match);
        } else {
            $routeResult->setNotFound($match[1] ?? []);
        }
        return parent::dispatchRequest($request->withAttribute(RouteResult::class, $routeResult));
    }

    /**
     * Set up middleware for a not found route
     *
     * @return void
     */
    protected function setNotFoundDecoratorMiddleware(): void
    {
        $middleware = $this->getStrategy()->getNotFoundDecorator(new NotFoundException());
        $strategy = $this->getStrategy();
        if ($strategy instanceof StrategyInterface && $strategy->isPrependThrowableDecorator() === false) {
            $this->middleware($middleware);
        } else {
            $this->prependMiddleware($middleware);
        }
    }

    /**
     * Set up middleware for a not allowed route
     *
     * @param array $allowed
     *
     * @return void
     */
    protected function setMethodNotAllowedDecoratorMiddleware(array $allowed): void
    {
        $middleware = $this->getStrategy()->getMethodNotAllowedDecorator(
            new MethodNotAllowedException($allowed)
        );
        $strategy = $this->getStrategy();
        if ($strategy instanceof StrategyInterface && $strategy->isPrependThrowableDecorator() === false) {
            $this->middleware($middleware);
        } else {
            $this->prependMiddleware($middleware);
        }
    }
}
