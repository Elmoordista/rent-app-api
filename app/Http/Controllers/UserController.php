<?php

namespace App\Http\Controllers;

use App\Models\Bookings;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public $model;
    public function __construct(
        User $model
    ){
        $this->model = $model;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $search = isset($request->search) ? $request->search : '';
            $type = isset($request->type) ? $request->type : '';
            $all = isset($request->all) ?true : false;

            $data = $this->model::query();

            $data->where('role','<>','admin');

            if($search){
                $data->where('name', 'like', '%' . $search . '%');
            }
            
            if($type){
                $data->where('role', $type);
            }
            if(!$all){
                $data = $data->paginate(10);
            }
            else{
                $data = $data->get();
            }

            return response()->json([
                'status' => true,
                'data'=> $data,
            ], 200); 
        } catch (\Exception $ex) {
            \Log::error('Error in CategoryController@index: ' . $ex->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the user.',
                'error' => $ex->getMessage(),
            ], 500);
        }
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
        try {
            $data = $request->all();
            $data['password'] = bcrypt($data['password']);
            if($request->id){
                $category = $this->model->find($request->id);
                if (!$category) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Category not found.',
                    ], 404);
                }
                $category->update($data);
            } else {
                $category = $this->model->create($data);
            }
            if (!$category) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to create category.',
                ], 500);
            }

            return response()->json([
                'status' => true,
            ], 200); 
        } catch (\Exception $ex) {
            \Log::error('Error in UserController@store: ' . $ex->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the user.',
                'error' => $ex->getMessage(),
            ], 500);
        }
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
       try {
            $category = $this->model->find($id);
            $category->delete();
            return response()->json([
                'status' => true,
            ], 200); 
        } catch (\Exception $ex) {
            \Log::error('Error in CategoryController@index: ' . $ex->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the user.',
                'error' => $ex->getMessage(),
            ], 500);
        }
    }

    public function getInfo(Request $request)
    {
        $user = Auth::user();
        return response()->json(['data' => $user], 200);
    }
    public function getProfileSettings(Request $request)
    {
        $user = Auth::user();
        $pendingBookings = Bookings::where('user_id', $user->id)
            ->whereIn('status', ['pending'])
            ->count();
        $favorites = Favorite::where('user_id', $user->id)->count();
        return response()->json(['data' => $user, 'pendingBookings' => $pendingBookings, 'favorites' => $favorites], 200);
    }

    public function getFavorites(Request $request)
    {
        $user = Auth::user();
        $favorites = Favorite::where('user_id', $user->id)
            ->with('item.images')
            ->get();
        return response()->json(['data' => $user, 'favorites' => $favorites], 200);
    }

    public function removeFavorite(Request $request, $id)
    {
        $user = Auth::user();
        $favorite = Favorite::where('user_id', $user->id)
            ->where('item_id', $id)
            ->first();
        if($favorite){
            $favorite->delete();
            return response()->json(['message' => 'Favorite removed', 'success' => true], 200);
        } else {
            return response()->json(['message' => 'Favorite not found', 'success' => false], 404);
        }
    }


    public function signup(Request $request)
    {
        $data = $request->all();
        $data['password'] = bcrypt($data['password']);
        $data['username'] = $data['email'];

        //check if email is already taken
        $existingUser = User::where('email', $data['email'])->first();
        if($existingUser){
            return response()->json(['message' => 'Email is already taken', 'success' => false], 400);
        }

        User::create($data);

        return response()->json(['message' => 'Signup successful', 'success' => true], 201);
    }
}
