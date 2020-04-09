<?php

namespace App\Models;

use Mezzio\Authentication\UserInterface;

class User implements UserInterface
{
    private $login;
    private $password;
    private $roles = ['administrator'];


    public function __construct($userDto = [])
    {
        $this->login = $userDto['login'] ?? '';
        $this->password = $userDto['password'] ?? '';
    }
    
    /**
     * Get the unique user identity (id, username, email address or ...)
     */
    public function getIdentity(): string
    {
        return $this->login;
    }
    
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Get all user roles
     *
     * @return Iterable
     */
    public function getRoles(): iterable
    {
        return $this->roles;
    }

    public function getDetail(string $name, $default = null)
    {
        return null;
    }

    public function getDetails(): array
    {
        return [];
    }
}
