<?php

namespace App\Middlewares;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Models\User;
use App\Models\UserManager;

class SessionAuthenticate implements MiddlewareInterface
{
    private $attribute = 'user';
    private $userManager;
    
    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }
    
    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $request->getAttribute('session');
        
        if ($session === null) {
            throw new InvalidArgumentException('Request must contain a session attribute');
        }
        $username = $session->get(User::KEY);
        if ($username === null) {
            return $handler->handle($request);
        }
        $user = $this->userManager->findUser($username);
        if ($user !== null) {
            $request = $request->withAttribute($this->attribute, $user);
        }
        return $handler->handle($request);
    }
}
