<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tryon_sesi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('pengguna_id');
            $table->string('foto_user_path');
            $table->unsignedBigInteger('produk_id')->default(0);
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');
            $table->text('pesan_error')->nullable();
            $table->string('foto_hasil_path')->nullable();
            $table->timestamp('consent_at');
            $table->timestamp('expires_at');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->foreign('pengguna_id')->references('pengguna_id')->on('pengguna')->onDelete('cascade');
            $table->index(['pengguna_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tryon_sesi');
    }
};
