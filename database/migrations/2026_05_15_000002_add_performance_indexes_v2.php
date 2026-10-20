<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambahkan performance indexes untuk production
     * Migration ini safe karena menggunakan IF NOT EXISTS
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        try {
            // transaksi indexes - menggunakan user_id (actual column)
            DB::statement("ALTER TABLE transaksi ADD INDEX IF NOT EXISTS idx_transaksi_user_status (user_id, status)");
            DB::statement("ALTER TABLE transaksi ADD INDEX IF NOT EXISTS idx_transaksi_kode (kode_transaksi)");
            DB::statement("ALTER TABLE transaksi ADD INDEX IF NOT EXISTS idx_transaksi_tanggal (tanggal)");

            // keranjang indexes
            DB::statement("ALTER TABLE keranjang ADD INDEX IF NOT EXISTS idx_keranjang_user (user_id)");

            // pembayaran indexes
            DB::statement("ALTER TABLE pembayaran ADD INDEX IF NOT EXISTS idx_pembayaran_status (status_pembayaran)");

            // pesanan indexes
            if (DB::getConnection()->getDoctrineSchemaManager()->tablesExist('pesanan')) {
                DB::statement("ALTER TABLE pesanan ADD INDEX IF NOT EXISTS idx_pesanan_status (status)");
            }

            // transaksi_detail indexes
            DB::statement("ALTER TABLE transaksi_detail ADD INDEX IF NOT EXISTS idx_transaksi_detail_transaksi (transaksi_id)");

            // rating_produk indexes
            if (DB::getConnection()->getDoctrineSchemaManager()->tablesExist('rating_produk')) {
                DB::statement("ALTER TABLE rating_produk ADD INDEX IF NOT EXISTS idx_rating_produk_produk (produk_id)");
                DB::statement("ALTER TABLE rating_produk ADD INDEX IF NOT EXISTS idx_rating_produk_user (user_id)");
            }

            // alamat_pengguna indexes
            if (DB::getConnection()->getDoctrineSchemaManager()->tablesExist('alamat_pengguna')) {
                DB::statement("ALTER TABLE alamat_pengguna ADD INDEX IF NOT EXISTS idx_alamat_user (user_id)");
            }

            // tracking_log indexes
            if (DB::getConnection()->getDoctrineSchemaManager()->tablesExist('tracking_log')) {
                DB::statement("ALTER TABLE tracking_log ADD INDEX IF NOT EXISTS idx_tracking_log_pesanan (pesanan_id)");
                DB::statement("ALTER TABLE tracking_log ADD INDEX IF NOT EXISTS idx_tracking_log_waktu (waktu)");
            }
        } catch (\Exception $e) {
            // Log but don't fail - indexes might already exist
            \Log::error('Performance indexes migration error: ' . $e->getMessage());
        } finally {
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        try {
            DB::statement("ALTER TABLE transaksi DROP INDEX IF EXISTS idx_transaksi_user_status");
            DB::statement("ALTER TABLE transaksi DROP INDEX IF EXISTS idx_transaksi_kode");
            DB::statement("ALTER TABLE transaksi DROP INDEX IF EXISTS idx_transaksi_tanggal");
            DB::statement("ALTER TABLE keranjang DROP INDEX IF EXISTS idx_keranjang_user");
            DB::statement("ALTER TABLE pembayaran DROP INDEX IF EXISTS idx_pembayaran_status");
            DB::statement("ALTER TABLE transaksi_detail DROP INDEX IF EXISTS idx_transaksi_detail_transaksi");
            
            if (DB::getConnection()->getDoctrineSchemaManager()->tablesExist('pesanan')) {
                DB::statement("ALTER TABLE pesanan DROP INDEX IF EXISTS idx_pesanan_status");
            }
            if (DB::getConnection()->getDoctrineSchemaManager()->tablesExist('rating_produk')) {
                DB::statement("ALTER TABLE rating_produk DROP INDEX IF EXISTS idx_rating_produk_produk");
                DB::statement("ALTER TABLE rating_produk DROP INDEX IF EXISTS idx_rating_produk_user");
            }
            if (DB::getConnection()->getDoctrineSchemaManager()->tablesExist('alamat_pengguna')) {
                DB::statement("ALTER TABLE alamat_pengguna DROP INDEX IF EXISTS idx_alamat_user");
            }
            if (DB::getConnection()->getDoctrineSchemaManager()->tablesExist('tracking_log')) {
                DB::statement("ALTER TABLE tracking_log DROP INDEX IF EXISTS idx_tracking_log_pesanan");
                DB::statement("ALTER TABLE tracking_log DROP INDEX IF EXISTS idx_tracking_log_waktu");
            }
        } catch (\Exception $e) {
            \Log::error('Performance indexes rollback error: ' . $e->getMessage());
        } finally {
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        }
    }
};
