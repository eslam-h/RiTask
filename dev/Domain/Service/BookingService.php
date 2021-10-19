<?php

namespace Dev\Domain\Service;

use Dev\Infrastructure\Repository\BookingRepository;
use Dev\Infrastructure\Repository\CapacityRepository;
use Illuminate\Support\Facades\DB;

class BookingService extends AbstractService
{
    private $capacityRepository;

    public function __construct(BookingRepository $repository, CapacityRepository $capacityRepository)
    {
        parent::__construct($repository);
        $this->capacityRepository = $capacityRepository;
    }

    public function getEachDayCapacity()
    {
        $allBookings = $this->repository->all();
        $dayBookingCapacity = [];
        foreach ($allBookings as $booking)
        {
            $arrivalDate = $booking['arrival_date'];
            $hotelId = $booking['hotel_id'];
            if (isset($dayBookingCapacity[$hotelId][$arrivalDate])) {
                $dayBookingCapacity[$hotelId][$arrivalDate] += 1;
            } else {
                $dayBookingCapacity[$hotelId][$arrivalDate] = 1;
            }
            for($i = $booking['nights']; $i > 1; $i--) {
                $nextDate = date('Y-m-d', strtotime($booking['arrival_date']. ' + 1 days'));
                if (isset($dayBookingCapacity[$hotelId][$nextDate])) {
                    $dayBookingCapacity[$hotelId][$nextDate] += 1;
                } else {
                    $dayBookingCapacity[$hotelId][$nextDate] = 1;
                }
            }
        }
        return $dayBookingCapacity;
    }

    public function getRejectedBookings()
    {
        $acceptedBookings = $this->getAcceptedBookings();
        $rejectedBookings = $this->repository->WhereNotIn('id', $acceptedBookings->pluck('id'))->get();
        return $rejectedBookings;
    }

    public function getAcceptedBookings()
    {
        $allBookings = $this->repository->orderBy('purchase_day', 'ASC')->orderBy('arrival_date', 'ASC')->get();
        $remainingCapacities = [];
        $acceptedBookings = collect();
        foreach ($allBookings as $booking) {
            $bookingHotelId = $booking->hotel_id;
            $bookingDate = $booking->arrival_date;
            $bookingNights = $booking->nights;
            $rejected = false;
            for(; $bookingNights > 0; $bookingNights--) {
                if (!isset($remainingCapacities[$bookingHotelId][$bookingDate])) {
                    $dayCapacity = $this->capacityRepository->where('date', $bookingDate)->where('hotel_id', $bookingHotelId)->first();
                    $remainingCapacities[$bookingHotelId][$bookingDate] = 0;
                    if ($dayCapacity) {
                        $remainingCapacities[$bookingHotelId][$bookingDate] = $dayCapacity->capacity;
                    }
                }
                if ($remainingCapacities[$bookingHotelId][$bookingDate] < 1) {
                    $rejected = true;
                }
                $remainingCapacities[$bookingHotelId][$bookingDate] -= 1;
                $bookingDate = date('Y-m-d', strtotime($booking->arrival_date . ' + 1 days'));
            }
            if (!$rejected) {
                $acceptedBookings[] = $booking;
            }
        }
        return $acceptedBookings;
    }

    public function getMostValuedCustomers($limit = 50)
    {
        $acceptedBookings = $this->getAcceptedBookings();
        $valuedCustomers = collect();
        foreach ($acceptedBookings as $booking) {
            if (!isset($valuedCustomers[$booking->customer_id])) {
                $valuedCustomers[$booking->customer_id] = floatval($booking->sales_price - $booking->purchase_price);
            } else {
                $valuedCustomers[$booking->customer_id] += floatval($booking->sales_price - $booking->purchase_price);
            }
        }
        return $valuedCustomers->sortDesc()->slice(0, $limit);
    }

    public function getMostValuedHotels($limit = 20)
    {
        $acceptedBookings = $this->getAcceptedBookings();
        $valuedHotels = collect();
        foreach ($acceptedBookings as $booking) {
            if (!isset($valuedHotels[$booking->hotel_id])) {
                $valuedHotels[$booking->hotel_id] = floatval($booking->sales_price - $booking->purchase_price);
            } else {
                $valuedHotels[$booking->hotel_id] += floatval($booking->sales_price - $booking->purchase_price);
            }
        }
        return $valuedHotels->sortDesc()->slice(0, $limit);
    }

    public function shouldVisitHotels()
    {
        $mostValuedCustomers = $this->getMostValuedCustomers();
        $visitedHotels = $this->repository->select('customer_id', DB::raw('GROUP_CONCAT(hotel_id) AS visited'))
            ->whereIn('customer_id', array_keys($mostValuedCustomers->toArray()))->groupBy('customer_id')->get();
        $mostValuedHotels = $this->getMostValuedHotels();
        $shouldVisitHotels = [];
        foreach ($visitedHotels as $visited) {
            $shouldVisitHotels[$visited->customer_id] = implode(',', array_diff(array_keys($mostValuedHotels->toArray()), explode(',', $visited->visited)));
        }
        return $shouldVisitHotels;
    }
}
