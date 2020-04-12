<?php

namespace App\Models;

class Pagination
{
    const PER_PAGE = 10;
    
    private $totalCount;
    private $page;
    private $perPage;
    private $args;
    private $routeName;

    public function __construct(int $totalCount, array $getParams, string $routeName)
    {
        $this->totalCount = $totalCount;
        $this->perPage = self::PER_PAGE;
        $page = isset($getParams['page']) ? (int)$getParams['page'] : 1;
        if ($page > $this->getPagesCount()) {
            $page = $this->getPagesCount();
        }
        $this->page = $page;
        unset($getParams['page']);
        $this->args = $getParams;
        $this->routeName = $routeName;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPagesCount(): int
    {
        return ceil($this->totalCount / $this->perPage);
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }
    
    public function setPerPage(int $value): self
    {
        $this->perPage = $value;
        return $this;
    }

    public function getLimit(): int
    {
        return $this->perPage;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }
    
    public function getRequestParams()
    {
        return $this->args;
    }
    
    public function getRouteName()
    {
        return $this->routeName;
    }
}
