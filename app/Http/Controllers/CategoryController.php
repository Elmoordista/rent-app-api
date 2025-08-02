<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public $model;

    public function __construct(Category $model)
    {
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
            $status = isset($request->status) ? $request->status : '';
            $all = isset($request->all) ?true : false;

            $data = $this->model::query();

            if($search){
                $data->where('name', 'like', '%' . $search . '%');
            }
            if($status && $status != 'all'){
                $data->where('status', $status);
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
            \Log::error('Error in CategoryController@store: ' . $ex->getMessage());
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
}
