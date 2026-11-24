<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait FileUpload
{
    public function uploadFile(UploadedFile $file, string $directory = 'uploads'): string
    {
        try {
            $filename = 'thinkcore_'.uniqid().'.'.$file->getClientOriginalExtension();
            $stored = $file->storeAs($directory, $filename, 'public');
            if ($stored === false) throw new \RuntimeException('Storage did not accept the upload.');

            return '/storage/'.$directory.'/'.$filename;
        } catch (\Exception $e) {
            // Handle the exception as needed, e.g., log it or rethrow it
            throw new \RuntimeException('File upload failed: '.$e->getMessage());
        }
    }

    public function deleteFile(?string $filePath): bool
    {
        if (! $filePath || ! str_starts_with($filePath, '/storage/')) {
            return false;
        }
        $relative = substr($filePath, 9);
        if (str_contains($relative, '..')) {
            return false;
        }

        return Storage::disk('public')->delete($relative);
    }
}
