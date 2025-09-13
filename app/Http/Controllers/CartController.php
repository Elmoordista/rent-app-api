<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $cartItems = Cart::with('item.images','item.variations', 'variation')
        ->where('user_id', $user->id)
        ->get();
        return response()->json(['success' => true, 'data' => $cartItems], 200);
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
        //check if item already in cart for user, if so update quantity instead of creating new entry
        $existingCartItem = Cart::where('user_id', Auth::id())
            ->where('item_id', $request->id)
            ->where('variation_id', $request->variation_id)
            ->first();

        if ($existingCartItem) {
            $existingCartItem->quantity += $request->quantity;
            $existingCartItem->variation_id = $request->variation_id;
            $existingCartItem->save();
        }
        else{
            Cart::create(
                [
                    'user_id' => Auth::id(),
                    'item_id' => $request->id,
                    'variation_id' => $request->variation_id,
                    'quantity' => $request->quantity,
                ]
            );
        }

        return response()->json(['success' => true], 200);

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
        //update quantity of cart item
        $cartItem = Cart::find($id);
        if ($cartItem && $cartItem->user_id == Auth::id()) {
            $cartItem->quantity = $request->quantity;
            $cartItem->save();
            return response()->json(['success' => true], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Item not found or unauthorized'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cartItem = Cart::find($id);
        if ($cartItem && $cartItem->user_id == Auth::id()) {
            $cartItem->delete();
            return response()->json(['success' => true], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Item not found or unauthorized'], 404);
        }
    }
}
