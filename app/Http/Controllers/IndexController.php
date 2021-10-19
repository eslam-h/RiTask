<?php

namespace App\Http\Controllers;

use Dev\Domain\Service\BookingService;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    private $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function shouldVisitHotels()
    {
        $shouldVisit = $this->bookingService->shouldVisitHotels();
        return view('index', compact('shouldVisit'));
    }

    public function rejectedBookings()
    {
        $rejectedBookings = $this->bookingService->getRejectedBookings();
        return view('rejected', compact('rejectedBookings'));
    }
}
