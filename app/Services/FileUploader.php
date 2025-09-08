<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class FileUploader
{
    public $default_folder = 'uploads/';

    public function storeFiles($id, $request, $folder)
    {
        $storage = Storage::disk('s3');

        $name = isset($request['file_name']) ? $request['file_name'] : Carbon::now()->timestamp . '.png';
        $image = isset($request['file_path']) ? $request['file_path'] : $request; // base64 encoded

        // Decode base64
        list($type, $image) = explode(';', $image);
        list(, $image) = explode(',', $image);
        $data = base64_decode($image);

        // Full path on S3
        $path = $this->default_folder . $folder . '/' . $id . '/' . $name;

        // Upload to S3 without ACL
        $upload = $storage->put($path, $data);

        if ($upload) {
            // Return temporary URL for private bucket
            return $path;
            // Or, if bucket is public:
            // return $storage->url($path);
        } else {
            return false;
        }
    }
}
