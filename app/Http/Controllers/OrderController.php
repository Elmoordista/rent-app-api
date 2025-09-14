<?php

namespace App\Http\Controllers;

use App\Models\BookingDetail;
use App\Models\Bookings;
use App\Models\Cart;
use App\Models\PaymentAccount;
use App\Services\FileUploader;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{

    public $fileUploader;
    public function __construct(
        FileUploader $fileUploader,
    ){
        $this->fileUploader = $fileUploader;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $g_cash_info = PaymentAccount::where('type','gcash')->first();
        $total_price = 0;
        $cartItems = $request->item_to_rent;
        $booking_details = $request->booking_details;
        $start_date = Carbon::parse($booking_details['pickupDate']);
        $end_date = Carbon::parse($booking_details['returnDate']);
        //calculate total days
        $total_days = $end_date->diffInDays($start_date);
        foreach($cartItems as $item) {
            if(isset($item['item_id'])) {
                //price per string to end and remove 
                $price_per_day = intval($item['pricePerDay']);
                $total_price += $price_per_day * $item['qty'];
            }
        }
        $total_price = $total_price * $total_days;
        if(isset($booking_details['selectedFileBase64']) && $booking_details['selectedFileBase64'] != null){
           $user_id = Auth::id();
           $drive_license = $this->fileUploader->storeFiles($user_id, $booking_details['selectedFileBase64'], 'uploads/driver_license');
           $booking_details['driver_license'] = $drive_license;
           unset($booking_details['selectedFileBase64']);
        }
        $bookingDetails = json_encode($booking_details);
        $data =  [
            'user_id' => Auth::id(),
            'start_date' => Carbon::parse($start_date)->toDateTimeString(),
            'end_date' => Carbon::parse($end_date)->toDateTimeString(),
            'total_price' => $total_price,
            'notes' => $booking_details ? $bookingDetails : null,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'delivery_info' => null,
            'payment_type' => $booking_details['paymentType'] ?? null,
            'delivery_option' => $booking_details['deliveryOption'] ?? null,
        ];
        $booking = Bookings::create($data);
        if(!$booking) {
            return response()->json(['message' => 'Order creation failed'], 500);
        }
        else{
            foreach($cartItems as $item) {
                if(isset($item['pricePerDay']) && isset($item['qty'])) {
                    BookingDetail::create([
                        'booking_id' => $booking->id,
                        'item_id' => $item['item_id'],
                        'variation_id' => $item['variation_id'] ?? null,
                        'quantity' => $item['qty'],
                        'price' => $item['pricePerDay'],
                    ]);
                    if(isset($item['cart_id'])){
                        Cart::where('id', $item['cart_id'])->delete();
                    }
                }
            }
        }
        $booking['total_days'] = $total_days;
        return response()->json(['booking' => $booking, 'gcash_info' => $g_cash_info, 'success' => true], 200);
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
        //
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

    public function getConfirmedOrders (Request $request)
    {
        $user = Auth::user();
        $status = isset($request->status) ? $request->status : '';
        $booking_items = BookingDetail::query();
       
        $booking_items->whereHas('booking', function($query) use ($user , $status) {
            $query->where('user_id', $user->id);
            $query->where('status', '<>', 'pending');
            // if($status != 'all'){
            //     $status = $status != 'active' ? $status : 'confirmed';
            //     $query->where(function($q) use ($status) {
            //         $q->where('status', $status);
            //     });
            // }
        })->with('item.images','booking','variation');

        $booking_items = $booking_items->get();

        return response()->json(['data' => $booking_items], 200);
    }

    public function cancelOrder ($id)
    {
        $user = Auth::user();
        $booking = Bookings::where('id', $id)
            ->where('user_id', $user->id)
            ->first();
        if(!$booking) {
            return response()->json(['message' => 'Order not found or cannot be cancelled'], 404);
        }
        $booking->status = 'cancelled';
        $booking->save();
        return response()->json(['message' => 'Order cancelled successfully'], 200);
    }
    
    public function getPendingOrders (Request $request)
    {
        $user = Auth::user();
        $status = isset($request->status) ? $request->status : 'pending';
        $pendingOrders = Bookings::where('user_id', $user->id)
            ->where('status', $status)
            ->with('booking_details.item.images','payments')
            ->get();
        return response()->json(['data' => $pendingOrders], 200);
    }
    public function getPendingOrdersDetails ($booking_id)
    {
        $pendingOrders = BookingDetail::where('booking_id', $booking_id)
            ->with('item.images')
            ->get();    
        return response()->json(['data' => $pendingOrders], 200);
    }
}
