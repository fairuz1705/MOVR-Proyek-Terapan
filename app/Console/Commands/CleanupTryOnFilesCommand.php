<?php

namespace App\Console\Commands;

use App\Models\TryOnSesi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupTryOnFilesCommand extends Command
{
    protected $signature = 'tryon:cleanup';
    protected $description = 'Menghapus otomatis foto pengguna & hasil try-on yang telah kedaluwarsa (> 24 jam)';

    public function handle()
    {
        $expiredSessions = TryOnSesi::where('expires_at', '<=', now())->get();
        $count = 0;

        foreach ($expiredSessions as $sesi) {
            if ($sesi->foto_user_path) Storage::disk('private')->delete($sesi->foto_user_path);
            if ($sesi->foto_hasil_path) Storage::disk('private')->delete($sesi->foto_hasil_path);
            $sesi->delete();
            $count++;
        }

        $this->info("Pembersihan selesai. $count sesi kedaluwarsa telah dihapus.");
    }
}
