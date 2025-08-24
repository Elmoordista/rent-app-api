<?php

namespace App\Http\Controllers;

use App\Models\ItemImages;
use App\Models\Items;
use App\Services\FileUploader;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public $model;
    public $model_images;
    public $fileUploader;
    public function __construct(
        Items $model,
        FileUploader $fileUploader,
        ItemImages $model_images
    ){
        $this->model = $model;
        $this->fileUploader = $fileUploader;
        $this->model_images = $model_images;
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
            $category = isset($request->category) ? $request->category : '';

            $data = $this->model::query();

            if($search){
                $data->where('name', 'like', '%' . $search . '%');
            }
            if($status && $status != 'all'){
                $data->where('status', $status);
            }
            if($category){
                $data->where('category_id', $category);
            }
            $data->with('images', 'owner', 'category')
                ->orderBy('created_at', 'desc');
            $data = $data->paginate(10);
            return response()->json([
                'status' => true,
                'data'=> $data,
            ], 200); 

        } catch (\Exception $ex) {
            \Log::error('Error in ItemController@index: ' . $ex->getMessage());
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
                $items = $this->model->find($request->id);
                if (!$items) {
                    return response()->json([
                        'status' => false,
                        'message' => 'items not found.',
                    ], 404);
                }
                $update = $items->update($data);
                if($update){
                    $filter_new_image = array_filter($request->images, function($image) {
                        return !isset($image['id']);
                    });
                    $filter_to_delete_image = array_filter($request->images, function($image) {
                        return isset($image['deleted']) && $image['deleted'];
                    });
                    if($filter_new_image) {
                        $this->uploadFile($items->id, $filter_new_image);
                    }
                    if($filter_to_delete_image) {
                        $this->deleteImage($filter_to_delete_image);
                    }
                }
            } else {
                $items = $this->model->create($data);
                if ($items) {
                    $this->uploadFile($items->id, $request->images);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Failed to create item.',
                    ], 500);
                }
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
        try {
            $item = $this->model->with('images', 'owner')->find($id);
            if (!$item) {
                return response()->json([
                    'status' => false,
                    'message' => 'Item not found.',
                ], 404);
            }
            return response()->json([
                'status' => true,
                'data' => $item,
            ], 200);
        } catch (\Exception $ex) {
            \Log::error('Error in ItemController@show: ' . $ex->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching the item.',
                'error' => $ex->getMessage(),
            ], 500);
        }
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
            $item = $this->model->find($id);
            if (!$item) {
                return response()->json([
                    'status' => false,
                    'message' => 'Item not found.',
                ], 404);
            }
            $item->delete();
            return response()->json([
                'status' => true,
                'message' => 'Item deleted successfully.',
            ], 200);
            
        } catch (\Exception $ex) {
            \Log::error('Error in ItemController@destroy: ' . $ex->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the item.',
                'error' => $ex->getMessage(),
            ], 500);
        }
    }

    public function uploadFile($item_id, $images)
    {
        if($images){
            foreach ($images as $key => $image) {
                $path = $this->fileUploader->storeFiles($key, $image, 'items');
                $this->model_images->create([
                    'item_id' => $item_id,
                    'image_path' => $path,
                    'image_type' => $image['file_type'],
                    'is_primary' => $key == 0 ? 1 : 0,
                    'image_size' => $image['file_size'],
                ]);
            }
        }
    }

    public function deleteImage($item_images)
    {
        if($item_images){
            foreach ($item_images as $key => $image) {
                $this->model_images->where('id', $image['id'])->delete();
                $file_path = public_path($image['image_url']);
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
        }
    }

    public function getItemsByCategory(Request $request, $id){
        try {
            $search = $request->input('search');
            $items = $this->model::query();
            if($id){
                $items->where('category_id', $id);
            }
            if($search){
                $items->where('name', 'like', '%' . $search . '%');
            }
            $items->with('images');
            $items = $items->get();
            return response()->json([
                'status' => true,
                'data' => $items,
            ], 200);
        } catch (\Exception $ex) {
            \Log::error('Error in CategoryController@getItemsByCategory: ' . $ex->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching items by category.',
                'error' => $ex->getMessage(),
            ], 500);
        }
    }
}
