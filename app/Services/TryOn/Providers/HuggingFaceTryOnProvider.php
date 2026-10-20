<?php

namespace App\Services\TryOn\Providers;

use App\Services\TryOn\TryOnProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HuggingFaceTryOnProvider implements TryOnProvider
{
    protected string $apiToken;

    public function __construct()
    {
        $this->apiToken = config('services.tryon.huggingface_token', '');
    }

    public function generateTryOn(string $humanPhotoAbsolutePath, string $garmentPhotoUrl, string $category): array
    {
        try {
            if (!file_exists($humanPhotoAbsolutePath)) {
                return ['success' => false, 'result_image_content' => null, 'error' => 'Foto tidak ditemukan.'];
            }

            // Call Hugging Face Gradio API space for IDM-VTON
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json',
            ])->timeout(30)->post('https://yisol-idm-vton.hf.space/api/predict', [
                'data' => [
                    'data:image/jpeg;base64,' . base64_encode(file_get_contents($humanPhotoAbsolutePath)),
                    $garmentPhotoUrl,
                    $category,
                    true, // is_checking
                    true, // is_auto_mask
                    30,   // denoise_steps
                ]
            ]);

            if ($response->failed() || empty($response->json('data.0'))) {
                Log::warning("HuggingFace space failed or sleeping. Falling back to Mock provider.", [
                    'body' => $response->body()
                ]);
                $mock = new MockTryOnProvider();
                return $mock->generateTryOn($humanPhotoAbsolutePath, $garmentPhotoUrl, $category);
            }

            $data = $response->json();
            $resultBase64 = $data['data'][0] ?? null;

            if (preg_match('/data:image\/(\w+);base64,(.*)/', $resultBase64, $matches)) {
                $imageContent = base64_decode($matches[2]);
            } else {
                $imageContent = base64_decode($resultBase64);
            }

            return [
                'success' => true,
                'result_image_content' => $imageContent,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::warning("HuggingFace TryOn Exception. Falling back to Mock provider.", ['message' => $e->getMessage()]);
            $mock = new MockTryOnProvider();
            return $mock->generateTryOn($humanPhotoAbsolutePath, $garmentPhotoUrl, $category);
        }
    }
}
