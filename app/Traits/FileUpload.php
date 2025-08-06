<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

trait FileUpload
{
    public function uploadFile(UploadedFile $file, string $directory = 'uploads'): string
    {
        $filename = 'thinkcore_'.uniqid().'.'.$file->getClientOriginalExtension();

        $file->move(public_path($directory), $filename);
        return '/'.$directory.'/'.$filename;
    }

    public function deleteFile(string $filePath): Bool
    {
        if (File::exists(public_path($filePath))) {
            File::delete(public_path($filePath));
            return true;
        }
        return false;
    }
}
