<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

trait FileUpload
{
    public function uploadFile(UploadedFile $file, string $directory = 'uploads'): string
    {
        try {
            $filename = 'thinkcore_'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->storeAs($directory, $filename, 'public');
            return '/'.$directory.'/'.$filename;
        } catch (\Exception $e) {
            // Handle the exception as needed, e.g., log it or rethrow it
            throw new \RuntimeException('File upload failed: '.$e->getMessage());
        }
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
