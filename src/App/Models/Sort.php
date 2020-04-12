<?php

namespace App\Models;

class Sort
{
    private $model;
    private $getParams;
    
    private $sort;
    private $sortParam = 'sort';

    public function __construct(array $getParams)
    {
        foreach ($getParams as $name => $value) {
            if ($name == $this->sortParam) {
                $this->sort = $value;
            }
        }
    }
    
    public function parseRawSort($allowAttributes)
    {
        [$direction, $attribute] = $this->parse($this->sort);
        $attribute = $this->validateAttribute($attribute, $allowAttributes);
        return ['attribute' => $attribute, 'direction' => $direction == SORT_ASC ? 'ASC' : 'DESC'];
    }
    
    public function parse($order, $defaultAttribute = 'id'): array
    {
        if ($order) {
            $direction = substr($order, 0, 1) == '-' ? SORT_DESC : SORT_ASC;
            $attribute = $direction == SORT_DESC ? substr($order, 1) : $order;
        } else {
            $direction = SORT_ASC;
            $attribute = $defaultAttribute;
        }
        return [$direction, $attribute];
    }
    
    public function build($attribute, $direction): string
    {
        $order = ($direction == SORT_ASC) ? $attribute : ('-' . $attribute);
        return $order;
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
    
    public function getSort()
    {
        return $this->sort;
    }
    
    public function getParam()
    {
        return $this->sortParam;
    }
}
