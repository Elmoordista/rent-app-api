<?php

namespace App\Http\Controllers;

use App\Models\Bookings;
use App\Models\Items;
use App\Models\Payments;
use App\Models\User;
use App\Services\FileUploader;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{

    public $fileUploader;
    public $model;
    public function __construct(
        FileUploader $fileUploader,
        Bookings $model
    ){
        $this->fileUploader = $fileUploader;
        $this->model = $model;
    }
    /**
     * Display a listing of the resource. 
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $status = isset($request->status) ? $request->status : null;
        $bookings = $this->model::query();
        if($status != 'all' && $status != null){
            $bookings = $bookings->where('status', $status);
        }
        $bookings->with('user','booking_details.item.images','payments', 'booking_details.variation');
        $bookings = $bookings->paginate(isset($request->per_page) ? $request->per_page : 10);

        return response()->json($bookings);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $booking = $this->model::where('id', $id)->first();
        if(!$booking){
            return response()->json(['message' => 'Booking not found', 'success' => false], 404);
        }
        $booking->update($request->all());
        return response()->json(['message' => 'Booking updated successfully', 'success' => true], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getFilteredBookings(Request $request)
    {
        $filter_type = $request->filterType ?? null;
        $day = $request->day ?? null;
        $month = $request->month ?? null;
        $date_from = $request->dateFrom ?? null;
        $date_to = $request->dateTo ?? null;
        $year = $request->year ?? null;

        $bookings = $this->model::query();

        // Apply filters
        if ($filter_type == 'Day' && $day) {
            $bookings->whereDate('created_at', Carbon::parse($day));
        } elseif ($filter_type == 'Monthly' && $month) {
            $bookings->whereMonth('created_at', Carbon::parse($month)->month)
                    ->whereYear('created_at', Carbon::parse($month)->year);
        } elseif ($filter_type == 'Date Range' && $date_from && $date_to) {
            $bookings->whereBetween('created_at', [Carbon::parse($date_from), Carbon::parse($date_to)]);
        } elseif ($filter_type == 'Yearly' && $year) {
            $bookings->whereYear('created_at', $year);
        }

        $bookings->whereIn('status', ['confirmed', 'completed']);
        $bookings = $bookings->get();

        $data = [];
        $categories = [];

        // Grouping
        if ($filter_type == 'Day') {
            $data[] = $bookings->sum('total_price');
            $categories[] = Carbon::parse($day)->format('Y-m-d');

        } elseif ($filter_type == 'Monthly') {
            $grouped = $bookings->groupBy(fn($row) => Carbon::parse($row->created_at)->format('d')); // "01" - "31"

            for ($i = 1; $i <= 31; $i++) {
                $day = sprintf('%02d', $i); // "01", "02"
                $data[] = isset($grouped[$day]) ? $grouped[$day]->sum('total_price') : 0;
                $categories[] = $day;
            }

        } elseif ($filter_type == 'Date Range') {
            $start = Carbon::parse($date_from);
            $end = Carbon::parse($date_to);
            $grouped = $bookings->groupBy(fn($row) => Carbon::parse($row->created_at)->format('Y-m-d'));

            $days = $start->diffInDays($end) + 1;
            for ($i = 0; $i < $days; $i++) {
                $date = $start->copy()->addDays($i)->format('Y-m-d');
                $data[] = isset($grouped[$date]) ? $grouped[$date]->sum('total_price') : 0;
                $categories[] = $date;
            }

        } elseif ($filter_type == 'Yearly') {
            $grouped = $bookings->groupBy(fn($row) => Carbon::parse($row->created_at)->format('m')); // "01"-"12"

            for ($i = 1; $i <= 12; $i++) {
                $month = sprintf('%02d', $i);
                $data[] = isset($grouped[$month]) ? $grouped[$month]->sum('total_price') : 0;
                $categories[] = $month;
            }
        }

        $total = array_sum($data);
        $users = User::count();
        $items = Items::count();
        $total_earnings = $this->model::whereIn('status', ['confirmed', 'completed'])->sum('total_price');
        $status_bookings_grouped = $this->model::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return response()->json([
            'data' => $data,
            'categories' => $categories,
            'total' => $total,
            'users' => $users,
            'items' => $items,
            'total_earnings' => $total_earnings,
            'status_bookings' => count($status_bookings_grouped) ? $status_bookings_grouped : [
                'pending' => 0,
                'confirmed' => 0,
                'completed' => 0,
                'cancelled' => 0
            ],
            'success' => true
        ]);
    }

    public function getPendings()
    {
        $pendingBookings = $this->model::where('status', 'pending')
        ->count();
        return response()->json(['data' => $pendingBookings, 'success' => true], 200);
    }
    public function uploadProofOfPayment(Request $request)
    {

        $file = $request['payment_proof'];
        $booking_id = $request['booking_id'];
        $path = $this->fileUploader->storeFiles($booking_id, $file, 'proof_of_payment');

        $booking = Bookings::where('id', $booking_id)->first();

        $payment_exists = Payments::where('booking_id', $booking_id)->first();
        if($payment_exists){
            Payments::where('booking_id', $booking_id)->update([
                'proof_of_payment' => $path,
            ]);
        }
        else{
            Payments::create([
                'booking_id' => $booking_id,
                'proof_of_payment' => $path,
                'amount' => $booking->total_price,
                'status' => 'pending',
                'paid_at' => Carbon::now(),
            ]);
        }
        return response()->json(['message' => 'Proof of payment uploaded successfully', 'success' => true], 200);
    }
}
