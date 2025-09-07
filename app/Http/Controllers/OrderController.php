<?php

namespace App\Http\Controllers;

use App\Models\BookingDetail;
use App\Models\Bookings;
use App\Models\Cart;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
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
       
        $total_price = 0;
        $cartItems = $request->item_to_rent;
        $booking_details = $request->booking_details;
        $start_date = $booking_details['pickupDate'];
        $end_date = $booking_details['returnDate'];
        //calculate total days
        $total_days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24);
        foreach($cartItems as $item) {
            if(isset($item['item_id'])) {
                //price per string to end and remove 
                $price_per_day = intval($item['pricePerDay']);
                $total_price += $price_per_day * $item['qty'];
            }
        }
        $total_price = $total_price * $total_days;
        $bookingDetails = json_encode($booking_details);
        $data =  [
            'user_id' => Auth::id(),
            'start_date' => Carbon::parse($start_date)->toDateTimeString(),
            'end_date' => Carbon::parse($end_date)->toDateTimeString(),
            'total_price' => $total_price,
            'notes' => $bookingDetails,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'delivery_info' => $booking_details ? $bookingDetails : null,
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
                        'quantity' => $item['qty'],
                        'price' => $item['pricePerDay'],
                    ]);
                    if(isset($item['cart_id'])){
                        Cart::where('id', $item['cart_id'])->delete();
                    }
                }
            }
        }
        return response()->json(['booking' => $booking, 'success' => true], 200);
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
        })->with('item.images','booking');

        $booking_items = $booking_items->get();

        return response()->json(['data' => $booking_items], 200);
    }
    
    public function getPendingOrders ()
    {
        $user = Auth::user();
        $pendingOrders = Bookings::where('user_id', $user->id)
            ->whereIn('status', ['pending'])
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
