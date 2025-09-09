<?php

namespace App\Http\Controllers;

use App\Models\Bookings;
use App\Models\Payments;
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
        $bookings->with('user','booking_details.item.images','payments');
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
