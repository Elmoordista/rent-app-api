<?php

namespace App\Services;
use Carbon\Carbon;

class FileUploader
{
    public $default_folder = 'uploads/';

    public function storeFiles($id , $request, $folder)
    {
        $name =  $request['file_name'];
        $image = $request['file_path'];  // your base64 encoded
        list($type, $image) = explode(';', $image);
        list(, $image)      = explode(',', $image);
        $data = base64_decode($image);
        $imageName = $name;
        $path = $folder .'/'. $id . '/' . $imageName;
        //make sure the directory exists
        if (!file_exists($this->default_folder . $folder . '/' . $id)) {
            mkdir($this->default_folder . $folder . '/' . $id, 0777, true);
        }
        $upload = file_put_contents($this->default_folder . $path, $data);
        if ($upload) {
            return $this->default_folder.$path;
        } else {
            return false;
        }
    }
}
