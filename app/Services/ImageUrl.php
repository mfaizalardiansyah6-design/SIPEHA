<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ImageUrl
{
    /**
     * Path legacy disimpan sebagai "uploads/settings/logo.png" (disk public).
     * Disk baru 'uploads' ber-root di public/uploads sehingga prefix "uploads/"
     * harus dibuang agar path tetap valid tanpa mengubah data lama di DB.
     */
    public static function normalize(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $path = ltrim($path, '/');

        return str_starts_with($path, 'uploads/') ? substr($path, strlen('uploads/')) : $path;
    }

    public static function url(?string $path): ?string
    {
        $path = static::normalize($path);

        return $path ? Storage::disk('uploads')->url($path) : null;
    }

    public static function delete(?string $path): void
    {
        $path = static::normalize($path);

        if ($path) {
            Storage::disk('uploads')->delete($path);
        }
    }
}
