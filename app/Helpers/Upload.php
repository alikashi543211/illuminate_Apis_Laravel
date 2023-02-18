<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

function uploadFile($file, $model, $column, $folderTitle = 'device-configurations' )
{
    $folderName = $folderTitle;
    // make file path structure
    $filePath = $folderName . '/' . date('Y') . '/' . date('m') . '/';
    //Set public folder path
    //renaming the file
    $name = $column . '_' . time() . '_' . rand(5000, 100000) . "." . $file->getClientOriginalExtension();
    if (env('AWS_ENV')) {
        Storage::disk('s3')->putFileAs($filePath, $file, $name);
    } else {
        $folderPath = public_path('/') . $filePath;
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        if (!$file->move($folderPath, $name)) {
            return false;
        }
    }
    $model->{$column} = $filePath . $name;
    return true;
}

function deleteFile($file)
{

    if (env('AWS_ENV')) {
        if (Storage::disk('s3')->delete($file)) {
            return true;
        } else return false;
    } else {
        if(File::exists($file))
        {

            File::delete($file);
            return true;
        }else
        {
            // dd("Ok", $file, parse_url($file, PHP_URL_PATH));
            return false;
        }
    }
}
