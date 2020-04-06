<?php

namespace App\Models;

class Sort
{
    private $model;
    private $getParams;
    
    private $rawSort;

    public function __construct(array $getParams)
    {
        foreach ($getParams as $name => $value) {
            if ($name == 'sort') {
                $this->rawSort = $value;
            }
        }
    }
    
    public function parseRawSort($allowAttributes)
    {
        $order = $this->rawSort;
        if ($order) {
            $direction = substr($order, 0, 1) == '-' ? 'DESC' : 'ASC';
            $attribute = $direction == 'DESC' ? substr($order, 1) : $order;
        } else {
            $direction = 'DESC';
            $attribute = 'id';
        }
        $attribute = $this->validateAttribute($attribute, $allowAttributes);
        return ['attribute' => $attribute, 'direction' => $direction];
    }
    
    public function getOrderByFor(array $allowAttributes): string
    {
        $order = $this->parseRawSort($allowAttributes);
        return implode(" ", $order);
    }
    
    public function validateAttribute(string $attribute, $allowAttributes, $default = 'id')
    {
        if (in_array($attribute, $allowAttributes)) {
            return $attribute;
        } else {
            return $default;
        }
    }
    
}