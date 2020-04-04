<?php

namespace App\Models;

class Pagination
{
    const PER_PAGE = 10;
    
    private $totalCount;
    private $page;
    private $perPage;
    private $args;

    public function __construct(int $totalCount, int $page, array $args)
    {
        $this->totalCount = $totalCount;
        $this->page = $page;
        $this->perPage = self::PER_PAGE;
        unset($args['page']);
        $this->args = $args;
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
    
    public function getArgs()
    {
        return $this->args;
    }
}