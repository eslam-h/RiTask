<?php

namespace Dev\Infrastructure\Repository;

use Dev\Infrastructure\Model\Booking;

class BookingRepository extends AbstractRepository
{
    public function __construct(Booking $model)
    {
        parent::__construct($model);
    }
}