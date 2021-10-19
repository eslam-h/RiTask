<?php

namespace Dev\Infrastructure\Repository;

use Dev\Infrastructure\Model\Capacity;

class CapacityRepository extends AbstractRepository
{
    public function __construct(Capacity $model)
    {
        parent::__construct($model);
    }
}