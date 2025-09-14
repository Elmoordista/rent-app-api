<?php

namespace App\Http\Controllers;

use App\Models\User;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        return response()->json(['user' => $user], 200);
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
        $credentials = $request->only('email', 'password');
       
        if (Auth::attempt($credentials)) {
            //create token and pass it
            $user = Auth::user();
            $token = Auth::user()->createToken('rentApp')->plainTextToken;
            return response()->json(['message' => 'Login successful', 'user' => $user, 'token' => $token], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 400);
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
    public function adminLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');
        //check if user is admin
        $user = User::where('email', $credentials['email'])->first();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        if (Auth::attempt($credentials)) {
            //create token and pass it
            $user = Auth::user();
            $token = Auth::user()->createToken('rentApp')->plainTextToken;
            return response()->json(['message' => 'Admin login successful', 'user' => $user, 'token' => $token, 'success' => true], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    
}
