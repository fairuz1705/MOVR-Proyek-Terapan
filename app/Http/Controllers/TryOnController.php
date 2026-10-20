<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateTryOnRequest;
use App\Http\Requests\UploadTryOnPhotoRequest;
use App\Jobs\ProcessTryOnJob;
use App\Models\Produk;
use App\Models\TryOnSesi;
use App\Services\TryOn\TryOnCategoryHelper;
use App\Services\TryOn\TryOnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TryOnController extends Controller
{
    public function upload(UploadTryOnPhotoRequest $request, TryOnService $service)
    {
        $user = Auth::user();

        // Check daily quota per user
        $dailyCount = TryOnSesi::where('pengguna_id', $user->pengguna_id)
            ->whereDate('created_at', now()->today())
            ->count();

        $maxQuota = config('services.tryon.max_per_day', 10);
        if ($dailyCount >= $maxQuota) {
            return response()->json([
                'message' => "Batas kuota harian mencoba pakaian ($maxQuota per hari) telah tercapai."
            ], 429);
        }

        $sesi = $service->processAndSaveUserPhoto(
            $request->file('foto'),
            $user->pengguna_id,
            $request->ip()
        );

        return response()->json([
            'sesi_id' => $sesi->id,
            'message' => 'Foto berhasil diunggah.',
        ]);
    }

    public function searchProducts(Request $request)
    {
        $query = $request->get('q', '');
        $categoryFilter = $request->get('category', '');

        $products = Produk::with(['gambarProduk', 'kategori'])
            ->where('is_active', 1)
            ->where('status_publish', 'publish')
            ->when($query, fn($q) => $q->where('nama_produk', 'like', "%{$query}%"))
            ->take(20)
            ->get()
            ->filter(function ($prod) use ($categoryFilter) {
                $tipe = TryOnCategoryHelper::determineType($prod);
                if ($tipe === 'unsupported') return false;
                if ($categoryFilter && $tipe !== $categoryFilter) return false;
                return true;
            })
            ->values()
            ->map(fn($p) => [
                'produk_id' => $p->produk_id,
                'nama_produk' => $p->nama_produk,
                'harga' => number_format($p->harga_dasar, 0, ',', '.'),
                'gambar_url' => $p->gambarProduk->first()?->url_lengkap ?? asset('images/placeholder.jpg'),
                'tryon_tipe' => TryOnCategoryHelper::determineType($p),
            ]);

        return response()->json(['data' => $products]);
    }

    public function generate(GenerateTryOnRequest $request)
    {
        $sesi = TryOnSesi::where('id', $request->sesi_id)
            ->where('pengguna_id', Auth::id())
            ->firstOrFail();

        $sesi->update(['produk_id' => $request->produk_id, 'status' => 'pending']);

        // Dispatch job asynchronously
        ProcessTryOnJob::dispatch($sesi->id);

        return response()->json(['message' => 'Proses AI Try-On telah dimulai.']);
    }

    public function checkStatus(string $id)
    {
        $sesi = TryOnSesi::where('id', $id)
            ->where('pengguna_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'status' => $sesi->status,
            'pesan_error' => $sesi->pesan_error,
        ]);
    }

    public function getResult(string $id)
    {
        $sesi = TryOnSesi::where('id', $id)
            ->where('pengguna_id', Auth::id())
            ->firstOrFail();

        if ($sesi->status !== 'done' || !$sesi->foto_hasil_path || !Storage::disk('private')->exists($sesi->foto_hasil_path)) {
            abort(404, 'Hasil belum tersedia.');
        }

        return Storage::disk('private')->response($sesi->foto_hasil_path);
    }

    public function deletePhoto(Request $request, ?string $id = null)
    {
        $sesiId = $id ?? $request->input('sesi_id');

        if ($sesiId) {
            $sesi = TryOnSesi::where('id', $sesiId)->where('pengguna_id', Auth::id())->first();
            if ($sesi) {
                if ($sesi->foto_user_path) Storage::disk('private')->delete($sesi->foto_user_path);
                if ($sesi->foto_hasil_path) Storage::disk('private')->delete($sesi->foto_hasil_path);
                $sesi->delete();
            }
        }

        return response()->json(['message' => 'Foto dan data try-on telah dihapus.']);
    }
}
