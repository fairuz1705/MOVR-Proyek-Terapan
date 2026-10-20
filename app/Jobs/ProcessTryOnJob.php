<?php

namespace App\Jobs;

use App\Models\Produk;
use App\Models\TryOnSesi;
use App\Services\TryOn\TryOnCategoryHelper;
use App\Services\TryOn\TryOnProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessTryOnJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 60;

    public function __construct(public string $sesiId) {}

    public function handle(TryOnProvider $provider): void
    {
        $sesi = TryOnSesi::find($this->sesiId);

        if (!$sesi || $sesi->status === 'done') {
            return;
        }

        $sesi->update(['status' => 'processing']);

        try {
            $humanPath = Storage::disk('private')->path($sesi->foto_user_path);
            $produk = Produk::with(['gambarProduk', 'kategori'])->find($sesi->produk_id);

            if (!$produk) {
                $sesi->update(['status' => 'failed', 'pesan_error' => 'Produk tidak ditemukan.']);
                return;
            }

            $category = TryOnCategoryHelper::determineType($produk);
            if ($category === 'unsupported') {
                $sesi->update(['status' => 'failed', 'pesan_error' => 'Produk ini tidak didukung untuk Try-On.']);
                return;
            }

            // Resolve garment photo local path or URL
            $garmentObj = $produk->gambarProduk->first();
            $garmentUrl = null;

            if ($garmentObj && !empty($garmentObj->url_gambar)) {
                $path = $garmentObj->url_gambar;
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    $garmentUrl = $path;
                } else {
                    $cleanPath = ltrim($path, '/');
                    if (str_starts_with($cleanPath, 'storage/')) {
                        $cleanPath = substr($cleanPath, 8);
                    }
                    if (Storage::disk('public')->exists($cleanPath)) {
                        $garmentUrl = Storage::disk('public')->path($cleanPath);
                    } elseif (file_exists(public_path($path))) {
                        $garmentUrl = public_path($path);
                    } else {
                        $garmentUrl = $garmentObj->url_lengkap;
                    }
                }
            }

            if (empty($garmentUrl)) {
                $garmentUrl = public_path('images/placeholder.jpg');
            }

            // Execute AI Provider
            $result = $provider->generateTryOn($humanPath, $garmentUrl, $category);

            if ($result['success'] && !empty($result['result_image_content'])) {
                $resultFilename = 'results/' . $sesi->id . '.webp';
                Storage::disk('private')->put('tryon/' . $resultFilename, $result['result_image_content']);

                $sesi->update([
                    'status' => 'done',
                    'foto_hasil_path' => 'tryon/' . $resultFilename,
                ]);
            } else {
                $sesi->update([
                    'status' => 'failed',
                    'pesan_error' => $result['error'] ?? 'Gagal menghasilkan visual AI.',
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("ProcessTryOnJob Exception", ['error' => $e->getMessage()]);
            $sesi->update([
                'status' => 'failed',
                'pesan_error' => 'Terjadi kesalahan sistem saat pemrosesan.',
            ]);
        }
    }
}
