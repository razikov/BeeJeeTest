<?php

namespace App\Models;

use Mezzio\Authentication\UserRepositoryInterface;
use Mezzio\Authentication\UserInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    private $users;
    
    public function __construct(array $users = ['admin' => 'admin'])
    {
        $this->users = $users;
    }
    
    /**
     * Authenticated user
     * @param string $username
     * @return User|null
     */
    public function findByCredential(string $username)
    {
        if (isset($this->users[$username])) {
            $userDto = [
                'login' => $username,
                'password' => $this->users[$username],
            ];
            return new User($userDto);
        }
        return null;
    }
    
    public function authenticate(string $credential, string $password = null): ?UserInterface
    {
        $user = $this->findByCredential($credential);
        if ($user && $user->getPassword() === $password) {
            return $user;
        }
        return null;
    }
}
