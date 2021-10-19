<?php

namespace Dev\Domain\Service;

use Dev\Infrastructure\Repository\AbstractRepository;

abstract class AbstractService
{
    protected $repository;

    public function __construct(AbstractRepository $repository)
    {
        $this->repository = $repository;
    }
}