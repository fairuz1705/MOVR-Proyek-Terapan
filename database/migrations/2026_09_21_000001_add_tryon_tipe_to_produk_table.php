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
        Schema::table('produk', function (Blueprint $table) {
            $table->enum('tryon_tipe', ['top', 'bottom', 'dress', 'outer', 'unsupported'])
                  ->nullable()
                  ->after('tags')
                  ->comment('Tipe pakaian untuk AI Virtual Try-On');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn('tryon_tipe');
        });
    }
};
