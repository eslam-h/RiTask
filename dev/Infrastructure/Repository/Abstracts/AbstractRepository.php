<?php

namespace Dev\Infrastructure\Repository;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function __call($name, $arguments)
    {
        return $this->model->{$name}(...$arguments);
    }
}