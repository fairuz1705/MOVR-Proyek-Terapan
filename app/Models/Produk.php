<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Schema;
use App\Models\WarnaProduk;
use App\Models\DetailProduk;
use Illuminate\Support\Str;
use App\Models\RatingProduk;

class Produk extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'produk_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // timestamps sesuai skema wajib
    const CREATED_AT = 'penyimpanan_waktu';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'supplier_id',
        'kategori_id',
        'nama_produk',
        'slug',
        'deskripsi',
        'spesifikasi',
        'gender',
        'tipe_olahraga',
        'tags',
        'harga_dasar',
        'total_terjual',
        'rata_rating',
        'jumlah_ulasan',
        'stok_minimum',
        'status_publish',
        'scheduled_at',
        'is_active',
        'is_featured',
        'tryon_tipe',
    ];

    protected $casts = [
        'tags' => 'array',
        'harga_dasar' => 'decimal:2',
        'rata_rating' => 'decimal:2',
        'scheduled_at' => 'datetime',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id', 'kategori_id');
    }

    public function gambarProduk(): HasMany
    {
        return $this->hasMany(GambarProduk::class, 'produk_id', 'produk_id');
    }

    public function gambarUtama(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(GambarProduk::class, 'produk_id', 'produk_id')
            ->orderBy('urutan', 'asc')
            ->orderBy('gambar_id', 'asc');
    }

    // Alias for existing views/controllers.
    public function images(): HasMany
    {
        return $this->gambarProduk();
    }

    // Warna list accessor — return collection of WarnaProduk related to this product
    // Uses detail_produk.warna_id when available; otherwise returns empty collection.
    public function getWarnaProdukAttribute()
    {
        // If detail_produk has a warna_id column, gather warna ids from variants
        if (Schema::hasColumn('detail_produk', 'warna_id')) {
            $warnaIds = DetailProduk::where('produk_id', $this->produk_id)
                ->whereNotNull('warna_id')
                ->pluck('warna_id')
                ->unique()
                ->values()
                ->all();

            if (empty($warnaIds)) {
                return collect();
            }

            return WarnaProduk::whereIn('warna_id', $warnaIds)->get();
        }

        // Fallback: no linkage available, return empty collection
        return collect();
    }

    public function detailProduk(): HasMany
    {
        return $this->hasMany(DetailProduk::class, 'produk_id', 'produk_id');
    }

    // Alias for existing views/controllers.
    public function details(): HasMany
    {
        return $this->detailProduk();
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(RatingProduk::class, 'produk_id', 'produk_id');
    }

    // ── Scopes ────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopePublished($query)
    {
        return $query->where('status_publish', 'publish');
    }

    // ── Accessors ─────────────────────────────────

    public function getHargaTerendahAttribute()
    {
        return $this->detailProduk()->min('harga');
    }

    public function getFormattedIdAttribute(): string
    {
        return 'PRD-' . str_pad((string) $this->produk_id, 6, '0', STR_PAD_LEFT);
    }

    public function getStatusStokAttribute(): string
    {
        $stokTotal = $this->relationLoaded('detailProduk')
            ? (int) $this->detailProduk->sum('stok')
            : (int) $this->detailProduk()->sum('stok');

        $minimum = (int) ($this->stok_minimum ?? 0);

        if ($stokTotal <= 0) {
            return 'out_of_stock';
        }

        if ($stokTotal <= $minimum) {
            return 'low_stock';
        }

        return 'available';
    }
}

