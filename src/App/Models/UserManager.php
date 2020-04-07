<?php

namespace App\Models;

class UserManager
{
    public const KEY = 'username';
    
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
    public function findUser(string $username)
    {
        if (isset($this->users[$username])) {
            return new User($username);
        }
        return null;
    }
    
    public function validatePassword(User $user, string $password = '')
    {
        return $this->users[$user->getName()] === $password;
    }
}
