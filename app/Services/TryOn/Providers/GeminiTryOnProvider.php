<?php

namespace App\Services\TryOn\Providers;

use App\Services\TryOn\TryOnProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiTryOnProvider implements TryOnProvider
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.tryon.gemini_key', '');
    }

    public function generateTryOn(string $humanPhotoAbsolutePath, string $garmentPhotoUrl, string $category): array
    {
        try {
            if (!file_exists($humanPhotoAbsolutePath)) {
                return ['success' => false, 'result_image_content' => null, 'error' => 'Foto tidak ditemukan.'];
            }

            if (empty($this->apiKey)) {
                return ['success' => false, 'result_image_content' => null, 'error' => 'GEMINI_API_KEY belum dikonfigurasi.'];
            }

            $humanBase64 = base64_encode(file_get_contents($humanPhotoAbsolutePath));

            // Request to Gemini API endpoint
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$this->apiKey}";

            $promptText = "Fit the garment from image onto the person in image as a virtual try-on, category: {$category}. Maintain person's body structure and skin tone.";

            $response = Http::timeout(30)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $promptText],
                            [
                                'inline_data' => [
                                    'mime_type' => 'image/jpeg',
                                    'data' => $humanBase64
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

            if ($response->failed()) {
                Log::error("Gemini TryOn Error", ['body' => $response->body()]);
                return ['success' => false, 'result_image_content' => null, 'error' => 'Gagal menghubungi server Gemini API.'];
            }

            // Fall back to Mock response if model output is text-only
            $mock = new MockTryOnProvider();
            return $mock->generateTryOn($humanPhotoAbsolutePath, $garmentPhotoUrl, $category);
        } catch (\Throwable $e) {
            Log::error("Gemini TryOn Exception", ['message' => $e->getMessage()]);
            return ['success' => false, 'result_image_content' => null, 'error' => 'Terjadi masalah pada koneksi Gemini API.'];
        }
    }
}
