<?php

namespace App\Services\TryOn;

use App\Models\Kategori;
use App\Models\Produk;

class TryOnCategoryHelper
{
    public static function determineType(Produk $produk): string
    {
        // 1. Explicit tryon_tipe column value if set
        if (!empty($produk->tryon_tipe)) {
            return $produk->tryon_tipe;
        }

        // 2. Check category hierarchy for KIDS or Accessories
        $kategori = $produk->kategori;
        if ($kategori) {
            $rootParentName = strtoupper(self::getRootCategoryName($kategori));
            if ($rootParentName === 'KIDS' || str_contains(strtoupper($kategori->nama_kategori), 'ACCESSORIES')) {
                return 'unsupported';
            }
        }

        // 3. Fallback Heuristic Keyword Match
        $searchText = strtolower(implode(' ', array_filter([
            $produk->nama_produk,
            is_array($produk->tags) ? implode(' ', $produk->tags) : ($produk->tags ?? ''),
            $produk->tipe_olahraga ?? '',
            $kategori ? $kategori->nama_kategori : ''
        ])));

        if (preg_match('/(celana|pants|shorts|skirt|rok|legging|jogger|bawahan)/i', $searchText)) {
            return 'bottom';
        }

        if (preg_match('/(dress|gaun|jumpsuit|romper|terusan)/i', $searchText)) {
            return 'dress';
        }

        if (preg_match('/(jaket|jacket|coat|blazer|cardigan|vest|luaran)/i', $searchText)) {
            return 'outer';
        }

        if (preg_match('/(kaos|t-shirt|shirt|jersey|polo|hoodie|top|baju|atasan)/i', $searchText)) {
            return 'top';
        }

        return 'top'; // Default fallback
    }

    protected static function getRootCategoryName(Kategori $kategori): string
    {
        $current = $kategori;
        while ($current->parent) {
            $current = $current->parent;
        }
        return $current->nama_kategori;
    }
}
