<?php

namespace App\Models;

class LoginForm
{
    private $users;
    
    public $login;
    public $password;
    
    public $isValid = false;
    public $isLoad = false;
    
    public function __construct(UserManager $userManager)
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
        $user = $this->users->findUser($this->login);
        if ($user) {
            $this->isValid = $this->users->validatePassword($user, $this->password);
        }
        return $this->isValid;
    }
}
