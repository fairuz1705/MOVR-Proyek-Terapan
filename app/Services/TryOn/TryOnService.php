<?php

namespace App\Services\TryOn;

use App\Models\TryOnSesi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TryOnService
{
    /**
     * Process & Save uploaded user photo (re-encode to strip EXIF/GPS metadata when GD is available)
     */
    public function processAndSaveUserPhoto(UploadedFile $file, int $penggunaId, string $ipAddress): TryOnSesi
    {
        $fileContent = file_get_contents($file->getRealPath());

        if (function_exists('imagecreatefromstring')) {
            $img = @imagecreatefromstring($fileContent);
            if ($img) {
                $filename = 'photos/' . Str::uuid() . '.webp';
                ob_start();
                imagewebp($img, null, 85);
                $encodedData = ob_get_clean();
                imagedestroy($img);

                Storage::disk('private')->put('tryon/' . $filename, $encodedData);

                return TryOnSesi::create([
                    'pengguna_id' => $penggunaId,
                    'foto_user_path' => 'tryon/' . $filename,
                    'produk_id' => 0,
                    'status' => 'pending',
                    'consent_at' => now(),
                    'expires_at' => now()->addHours(24),
                    'ip_address' => $ipAddress,
                ]);
            }
        }

        // Fallback for CLI/environments without GD extension
        $filename = 'photos/' . Str::uuid() . '.' . ($file->getClientOriginalExtension() ?: 'jpg');
        Storage::disk('private')->put('tryon/' . $filename, $fileContent);

        return TryOnSesi::create([
            'pengguna_id' => $penggunaId,
            'foto_user_path' => 'tryon/' . $filename,
            'produk_id' => 0,
            'status' => 'pending',
            'consent_at' => now(),
            'expires_at' => now()->addHours(24),
            'ip_address' => $ipAddress,
        ]);
    }
}
