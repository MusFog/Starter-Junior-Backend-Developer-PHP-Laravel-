<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ImageService
{
    public static function handleImageUpload(UploadedFile $file): string
    {
        $image = Image::make($file->getRealPath());

        @exif_read_data($file->getRealPath()); // if image is .jpg and have EXIF

        $image->fit(300, 300)->encode('jpg', 80);

        $filename = uniqid() . '.jpg';
        $relativePath = "employees/photos/{$filename}";
        $image->save(storage_path("app/public/{$relativePath}"));

        return $relativePath;
    }


    public static function deleteImage(?string $image_path): bool
    {
        if ($image_path && Storage::disk('public')->exists($image_path)) {
            Storage::disk('public')->delete($image_path);
            return true;
        } else {
            return false;
        }
    }

    public static function checkImage(?string $image_path): string | null
    {
        if ($image_path && Storage::disk('public')->exists($image_path)) {
            return $image_path;
        } else {
            return $image_path = null;
        }
    }
}
