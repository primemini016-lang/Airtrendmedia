<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use RuntimeException;

/**
 * Centralized public-image upload pipeline.
 *
 * - Validated uploads are normalized to WebP for reliable browser display.
 * - EXIF orientation is corrected when the driver supports it.
 * - Large images are down-scaled before encoding.
 * - The original upload is retained as a safe fallback if the image driver
 *   cannot decode a particular file, preventing an upload from becoming a 500.
 * - Files are written to the application's public storage disk.
 */
class MediaUploadService
{
    public function storeImage(UploadedFile $file, string $directory, int $maxWidth = 1800, int $quality = 84): string
    {
        $directory = trim($directory, '/');
        $base = Str::random(24);

        try {
            $manager = $this->manager();
            $image = $manager->read($file->getRealPath());
            if (method_exists($image, 'orient')) {
                $image->orient();
            }
            $image->scaleDown(width: $maxWidth);

            $path = ($directory !== '' ? $directory.'/' : '').$base.'.webp';
            Storage::disk('public')->put($path, (string) $image->toWebp($quality));
            return $path;
        } catch (\Throwable $e) {
            // Do not turn a valid upload into a fatal/500 just because a host's
            // image driver cannot decode a particular source image.
            Log::warning('Airtrendmedia image normalization failed; storing original upload.', [
                'directory' => $directory,
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'error' => $e->getMessage(),
            ]);

            $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
            if (! preg_match('/^(jpe?g|png|gif|webp|bmp)$/i', $extension)) {
                throw new RuntimeException('The uploaded image could not be processed on this server.');
            }

            $path = ($directory !== '' ? $directory.'/' : '').$base.'.'.$extension;
            Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));
            return $path;
        }
    }

    public function storeAvatar(UploadedFile $file, string|int $userId): string
    {
        try {
            $manager = $this->manager();
            $image = $manager->read($file->getRealPath());
            if (method_exists($image, 'orient')) {
                $image->orient();
            }
            $image->cover(320, 320);
            $path = 'avatars/avatar_'.$userId.'_'.Str::random(16).'.webp';
            Storage::disk('public')->put($path, (string) $image->toWebp(88));
            return $path;
        } catch (\Throwable $e) {
            Log::warning('Airtrendmedia avatar normalization failed; storing original upload.', [
                'user_id' => $userId,
                'name' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            if (! preg_match('/^(jpe?g|png|gif|webp|bmp)$/i', $extension)) {
                throw new RuntimeException('The profile image could not be processed on this server.');
            }
            $path = 'avatars/avatar_'.$userId.'_'.Str::random(16).'.'.$extension;
            Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));
            return $path;
        }
    }

    public function storeProof(UploadedFile $file): string
    {
        try {
            $manager = $this->manager();
            $image = $manager->read($file->getRealPath());
            if (method_exists($image, 'orient')) {
                $image->orient();
            }
            $image->scaleDown(width: 1400);
            $path = 'proofs/proof_'.Str::random(24).'.webp';
            Storage::disk('public')->put($path, (string) $image->toWebp(82));
            return $path;
        } catch (\Throwable $e) {
            Log::warning('Airtrendmedia proof image normalization failed; storing original.', [
                'name' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);
            $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            if (!preg_match('/^(jpe?g|png|gif|webp|bmp)$/i', $extension)) {
                throw new RuntimeException('The proof image could not be processed on this server.');
            }
            $path = 'proofs/proof_'.Str::random(24).'.'.$extension;
            Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));
            return $path;
        }
    }

    private function manager(): ImageManager
    {
        if (extension_loaded('gd')) {
            return new ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        }
        if (extension_loaded('imagick')) {
            return new ImageManager(new \Intervention\Image\Drivers\Imagick\Driver());
        }
        throw new RuntimeException('Image processing requires GD or Imagick.');
    }
}
