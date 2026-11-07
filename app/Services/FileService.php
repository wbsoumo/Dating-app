<?php

namespace App\Services;

use App\Models\Photo;
use App\Models\User;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FileService
{
    /**
     * Store uploaded profile photo in public/uploads/photos directory.
     * Compatible with cPanel shared hosting without needing symlink execution.
     */
    public function uploadPhoto(User $user, UploadedFile $file, bool $isPrimary = false): Photo
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and WebP images are allowed.');
        }

        // 5MB limit
        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new Exception('File size exceeds the 5MB limit.');
        }

        $uploadDir = public_path('uploads/photos/' . $user->id);
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        $filename = 'photo_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move($uploadDir, $filename);

        $relativePath = 'uploads/photos/' . $user->id . '/' . $filename;
        $url = url($relativePath);

        // If marked primary, unset other primary photos
        if ($isPrimary || $user->photos()->count() === 0) {
            $user->photos()->update(['is_primary' => false]);
            $isPrimary = true;
        }

        $order = $user->photos()->count();

        return Photo::create([
            'user_id' => $user->id,
            'url' => $url,
            'path' => $relativePath,
            'thumbnail_url' => $url,
            'is_primary' => $isPrimary,
            'sort_order' => $order,
            'is_approved' => true,
        ]);
    }

    /**
     * Delete a photo record and its local disk file.
     */
    public function deletePhoto(Photo $photo): bool
    {
        if (!empty($photo->path)) {
            $fullPath = public_path($photo->path);
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
        }

        $userId = $photo->user_id;
        $wasPrimary = $photo->is_primary;

        $photo->delete();

        // If the deleted photo was primary, assign the first remaining photo as primary
        if ($wasPrimary) {
            $nextPhoto = Photo::where('user_id', $userId)->orderBy('sort_order', 'asc')->first();
            if ($nextPhoto) {
                $nextPhoto->update(['is_primary' => true]);
            }
        }

        return true;
    }
}
