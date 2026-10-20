<?php

namespace App\Services\TryOn;

interface TryOnProvider
{
    /**
     * Jalankan proses AI Virtual Try-On
     *
     * @param string $humanPhotoAbsolutePath Path file foto manusia di storage lokal
     * @param string $garmentPhotoUrl URL atau Path foto pakaian
     * @param string $category Tipe kategori (top, bottom, dress, outer)
     * @return array ['success' => bool, 'result_image_content' => string|null, 'error' => string|null]
     */
    public function generateTryOn(string $humanPhotoAbsolutePath, string $garmentPhotoUrl, string $category): array;
}
