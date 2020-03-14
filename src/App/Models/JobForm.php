<?php

namespace App\Models;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;

class JobForm
{
    public $name;
    public $email;
    public $content;
    public $status;
    
    public $errors = [];
    public $isLoad = False;

    public function __construct($params = [])
    {
        foreach ($params as $attribute => $value) {
            if (property_exists($this, $attribute)) {
                if ($attribute == 'status') {
                    $this->$attribute = (bool)$value;
                } else {
                    $this->$attribute = $value;
                }
            }
        }
    }
    
    public function load($params)
    {
        $this->status = false;
        foreach ($params as $attribute => $value) {
            if (property_exists($this, $attribute)) {
                if ($attribute == 'status') {
                    $this->$attribute = (bool)$value;
                } else {
                    $this->$attribute = $value;
                }
                $this->isLoad = True;
            }
        }
        return $this->isLoad;
    }
    
    public function rules()
    {
        return [
            'name' => [
                new Length([
                    'max' => 50,
                    'minMessage' => 'Значение не может быть меньше, чем {{ limit }} символов',
                    'maxMessage' => 'Значение не может быть больше, чем {{ limit }} символов',
                ]),
                new NotBlank([
                    'message' => 'Поле не может быть пустым.',
                ]),
            ],
            'email' => [
                new Email([
                    'message' => 'Email "{{ value }}" не соответствует шаблону.',
                ]),
                new NotBlank([
                    'message' => 'Поле не может быть пустым.',
                ]),
            ],
            'status' => [
                new Type([
                    'type' => 'bool',
                    'message' => 'Значение должно быть логического типа.',
                ]),
            ],
            'content' => [
                new Length([
                    'max' => 255,
                    'minMessage' => 'Значение не может быть меньше, чем {{ limit }} символов',
                    'maxMessage' => 'Значение не может быть больше, чем {{ limit }} символов',
                ]),
                new NotBlank([
                    'message' => 'Поле не может быть пустым.',
                ]),
            ],
        ];
    }
    
    
    public function validate()
    {
        $this->errors = [];
        foreach ($this->rules() as $attribute => $rules) {
            if (!empty($this->validateAttribute($attribute, $rules))) {
                $this->errors[$attribute] = $this->validateAttribute($attribute, $rules);
            }
        }
        return empty($this->errors);
    }
    
    private function validateAttribute($attribute, $rules)
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($this->$attribute, $rules);

        $errors = [];
        if (0 !== count($violations)) {
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
        }
        return $errors;
    }
    
    public function getAttributeErrors($attribute)
    {
        return $this->errors[$attribute] ?? null;
    }
    
    public function getDto()
    {
        return [
            'id' => null,
            'name' => $this->name,
            'email' => $this->email,
            'content' => $this->content,
            'status' => $this->status,
        ];
    }

}