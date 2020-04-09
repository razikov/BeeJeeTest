<?php

namespace App\Middlewares;

use Mezzio\Csrf\CsrfGuardFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

class CsrfMiddleware implements MiddlewareInterface
{
    const GUARD_ATTRIBUTE = 'csrf';

    /**
     * @var string
     */
    private $attributeKey;

    /**
     * @var CsrfGuardFactoryInterface
     */
    private $guardFactory;

    public function __construct(
        CsrfGuardFactoryInterface $guardFactory,
        string $attributeKey = self::GUARD_ATTRIBUTE
    ) {
        $this->guardFactory = $guardFactory;
        $this->attributeKey = $attributeKey;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $guard = $this->guardFactory->createGuardFromRequest($request);
        
        if ($request->getMethod() === 'POST') {
            $data  = $request->getParsedBody();
            $token = $data['__csrf'] ?? '';
            if (!$guard->validateToken($token)) {
                $request->getAttribute('flash')->flash('error', 'Данные формы не приняты. Заполните ещё раз');
                return new \Laminas\Diactoros\Response\RedirectResponse($request->getUri()->getPath());
            }
        }
        return $handler->handle($request->withAttribute($this->attributeKey, $guard));
    }
}
