<?php

namespace App\Models;

class LoginForm
{
    private $users;
    
    public $login;
    public $password;
    
    public $isValid = false;
    public $isLoad = false;
    
    public function __construct(UserRepository $userManager)
    {
        $this->users = $userManager;
    }
    
    public function load($params)
    {
        foreach ($params as $attribute => $value) {
            if (property_exists($this, $attribute)) {
                $this->$attribute = $value;
                $this->isLoad = true;
            }
        }
        return $this->isLoad;
    }
    
    public function validate()
    {
        return $this->users->authenticate($this->login, $this->password) !== null;
    }
}
