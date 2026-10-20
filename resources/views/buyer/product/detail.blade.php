@extends('layouts.buyer')
@section('title', $product->nama_produk . ' — MOVR')
@section('content')

@php
  $tryOnCategoryType = \App\Services\TryOn\TryOnCategoryHelper::determineType($product);
  $isTryOnSupported = ($tryOnCategoryType !== 'unsupported');

  $variantImages = collect($product->details ?? $product->detailProduk ?? [])
    ->filter(fn($detail) => !is_null($detail->warna_id))
    ->groupBy('warna_id')
    ->map(function ($detailsByColor) {
      foreach ($detailsByColor as $detail) {
        $img = $detail->gambarDetail->first();
        if ($img && $img->url_lengkap) {
          return $img->url_lengkap;
        }
      }
      return null;
    })
    ->filter()
    ->values()
    ->all();

  $galleryImages = collect($product->images ?? $product->gambarProduk ?? [])
    ->map(function ($img) {
      return $img->url_lengkap ?? $img->url_safe ?? null;
    })
    ->filter()
    ->concat($variantImages)
    ->unique()
    ->values()
    ->all();

  $activeImage = $galleryImages[0]
    ?? optional($product->gambarUtama)->url_lengkap
    ?? optional($product->gambarUtama)->url_safe
    ?? asset('images/placeholder.png');

  $variantItems = collect($product->details ?? $product->detailProduk ?? [])
    ->map(function ($detail) {
      return [
        'detail_produk_id' => $detail->detail_produk_id,
        'warna_id' => $detail->warna_id,
        'nama_warna' => $detail->warna?->nama_warna,
        'kode_hex' => $detail->warna?->kode_hex,
        'ukuran' => $detail->ukuran,
        'stok' => (int) ($detail->stok ?? 0),
        'harga' => (float) ($detail->harga ?? 0),
        'gambar_url' => $detail->gambarDetail->first()?->url_lengkap,
      ];
    })
    ->values();

  $warnaOptions = $variantItems
    ->filter(fn ($item) => !is_null($item['warna_id']))
    ->unique('warna_id')
    ->values();

  $hasWarna = $warnaOptions->isNotEmpty();
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
  {{-- Breadcrumb --}}
  <div class="flex items-center gap-2 text-sm text-gray-400 mb-8">
    <a href="{{ route('home') }}" class="hover:text-[#63A2BB] transition">
      Beranda
    </a>
    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
    </svg>
    <a href="{{ route('category.show', $product->kategori->slug) }}" 
       class="hover:text-[#63A2BB] transition">
      {{ $product->kategori->nama_kategori }}
    </a>
    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"/>
    </svg>
    <span class="text-gray-700 font-medium line-clamp-1">
      {{ $product->nama_produk }}
    </span>
  </div>

  {{-- Product Detail Grid --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 xl:gap-12 items-start">
    {{-- Left: Gallery --}}
    <div class="lg:col-span-1"
         x-data="{
           images: @js($galleryImages),
           activeImage: @js($activeImage),
           init() {
             if (!this.activeImage && this.images.length > 0) {
               this.activeImage = this.images[0];
             }
           }
         }"
         @change-active-image.window="activeImage = $event.detail.image">
      {{-- Main Image --}}
            <div class="relative bg-[#F8FAFB] rounded-2xl overflow-hidden aspect-square flex items-center justify-center group border border-slate-100">
        <img :src="activeImage"
             :alt="@json($product->nama_produk)"
              class="w-full h-full object-contain object-center group-hover:scale-105 transition-transform duration-500">
        
        {{-- Badge --}}
        @if($product->is_featured)
        <div class="absolute top-4 left-4 bg-[#63A2BB] 
                    text-white px-4 py-2 rounded-full 
                    text-xs font-bold uppercase">
          Unggulan
        </div>
        @endif

        {{-- Wishlist Button (Floating) --}}
        <div class="absolute top-4 right-4 z-30"
             x-data="{ 
               isWishlisted: @json(auth()->check() && \App\Models\Wishlist::where('pengguna_id', auth()->id())->where('produk_id', $product->produk_id)->exists()),
               loading: false,
               async toggle() {
                 this.loading = true;
                 const res = await fetch('{{ route('wishlist.toggle') }}', {
                   method: 'POST',
                   headers: {
                     'Content-Type': 'application/json',
                     'Accept': 'application/json',
                     'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                   },
                   body: JSON.stringify({ produk_id: {{ $product->produk_id }} })
                 });
                 const data = await res.json();
                 if (data.success) {
                   this.isWishlisted = !this.isWishlisted;
                   window.dispatchEvent(new Event('wishlist-updated'));
                   showToast(this.isWishlisted ? @json(__('ui.product_added_wishlist')) : @json(__('ui.product_removed_wishlist')));
                 }
                 this.loading = false;
               }
             }">
          <button @click.stop.prevent="toggle()"
                  :disabled="loading"
                  class="w-12 h-12 rounded-full bg-white shadow-lg 
                         flex items-center justify-center 
                         hover:scale-110 transition-transform">
            <svg class="w-6 h-6"
                 :fill="isWishlisted ? '#EF4444' : 'none'"
                 :stroke="isWishlisted ? '#EF4444' : '#63A2BB'"
                 stroke-width="2"
                 viewBox="0 0 24 24">
              <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 
                       20.364l7.682-7.682a4.5 4.5 0 00
                       -6.364-6.364L12 7.636l-1.318-1.318a4.5 
                       4.5 0 00-6.364 0z"/>
            </svg>
          </button>
        </div>
      </div>

      <div class="grid grid-cols-4 gap-2 mt-3">
        <template x-for="(img, idx) in images" :key="idx">
          <button type="button"
                  @click="activeImage = img"
                  :class="{
                    'ring-2 ring-[#63A2BB] border-[#63A2BB]': activeImage === img
                  }"
                  class="aspect-square rounded-xl overflow-hidden bg-gray-50 border-2 border-gray-200 hover:border-[#63A2BB] transition">
            <img :src="img"
                 class="w-full h-full object-cover object-center">
          </button>
        </template>
      </div>
    </div>

    {{-- Right: Info & Checkout --}}
    <div class="lg:col-span-1 flex flex-col gap-4"
         x-data="productDetailState(@js($variantItems), @js($warnaOptions), @js($hasWarna))">
      {{-- Product Name --}}
      <div>
        <h1 class="text-2xl sm:text-3xl font-black text-gray-900 mb-2">
          {{ $product->nama_produk }}
        </h1>
        <p class="text-gray-500 text-sm">
          Kode: {{ $product->kode_produk }}
        </p>
      </div>

      {{-- Rating --}}
      <div class="flex items-center gap-3 py-4 border-b-2 border-gray-100">
        <div class="flex items-center gap-1">
          @for($i = 0; $i < 5; $i++)
            @if($i < floor($ratingStats['average'] ?? 0))
              <svg class="w-4 h-4 text-amber-400 fill-amber-400" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
              </svg>
            @else
              <svg class="w-4 h-4 text-gray-300 fill-gray-300" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
              </svg>
            @endif
          @endfor
        </div>
        <span class="font-bold text-sm text-gray-700">
          {{ number_format($ratingStats['average'] ?? 0, 1) }}
        </span>
        <span class="text-xs text-gray-500">
          ({{ $ratingStats['total'] ?? 0 }} ulasan)
        </span>
      </div>

      {{-- Price Section --}}
      <div class="py-4">
        @php
          $minPrice = $product->harga_dasar;
          $hasPromo = !empty($promoAktif);
          $promoNominal = 0;
          if ($hasPromo) {
            $promoNominal = (float)($promoAktif->nominal_diskon ?? 0);
            if ($promoNominal <= 0 && !empty($promoAktif->persen_diskon)) {
              $promoNominal = $minPrice * ($promoAktif->persen_diskon / 100);
            }
          }
          $hargaFinal = max(0, $minPrice - $promoNominal);
        @endphp
        <div class="flex items-baseline gap-3">
          <span class="text-3xl font-black text-[#63A2BB]">
            Rp {{ number_format($hargaFinal, 0, ',', '.') }}
          </span>
          @if($hasPromo)
            <span class="text-lg line-through text-gray-400">
              Rp {{ number_format($minPrice, 0, ',', '.') }}
            </span>
            <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">
              -{{ $promoAktif->persen_diskon ?? 0 }}%
            </span>
          @endif
        </div>
      </div>

      {{-- Color Selection --}}
      @if($hasWarna)
      <div class="py-4 border-b-2 border-gray-100">
        <h3 class="text-sm font-bold text-gray-900 mb-1">
          Pilih Warna
        </h3>
        <p class="text-xs text-gray-500 mb-3">
          Pilih varian yang tersedia sebelum menentukan ukuran.
        </p>
        <div class="flex gap-3 flex-wrap">
          <template x-for="warna in warnas" :key="warna.warna_id">
            <button @click="selectWarna(warna.warna_id)"
                    :class="{ 'ring-2 ring-[#63A2BB]': selectedWarna === warna.warna_id }"
                    class="flex items-center gap-2 px-3 py-2 rounded-full 
                           border-2 border-gray-200 hover:border-[#63A2BB] 
                           transition text-xs font-medium">
              <div class="w-5 h-5 rounded-full border-2 border-gray-300"
                   :style="{ backgroundColor: warna.kode_hex }">
              </div>
              <span x-text="warna.nama_warna"></span>
            </button>
          </template>
        </div>
      </div>
      @endif

      {{-- Size Selection --}}
      <div class="py-4 border-b-2 border-gray-100">
        <h3 class="text-sm font-bold text-gray-900 mb-1">
          Pilih Ukuran
        </h3>
        <p class="text-xs text-gray-500 mb-3">
          Stok per ukuran ditampilkan di bawah nama ukuran.
        </p>
        <div class="flex gap-2 flex-wrap">
          <template x-for="ukuran in ukurans" :key="ukuran.detail_produk_id">
            <button @click="selectUkuran(ukuran.detail_produk_id)"
                    :disabled="ukuran.stok === 0"
                    :class="{ 
                      'bg-[#63A2BB] text-white ring-2 ring-[#63A2BB]': selectedDetail && selectedDetail.detail_produk_id === ukuran.detail_produk_id,
                      'opacity-50 cursor-not-allowed': ukuran.stok === 0
                    }"
                    class="min-w-[76px] px-4 py-2.5 border-2 border-gray-200 rounded-lg
                           text-sm font-bold hover:border-[#63A2BB] transition flex flex-col items-center leading-tight">
              <span x-text="ukuran.ukuran"></span>
              <span class="text-xs font-medium mt-1" x-text="ukuran.stok > 0 ? `${ukuran.stok} stok` : 'Habis'"></span>
            </button>
          </template>
        </div>
      </div>

      <div class="py-4 border-b-2 border-gray-100" x-show="selectedDetail !== null" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <h3 class="text-sm font-bold text-gray-700 mb-3">
          {{ __('ui.qty') }}
        </h3>
        <div class="flex items-center gap-4">
          <div class="flex items-center border-2 border-gray-200 rounded-2xl overflow-hidden bg-white">
            <button type="button" @click="qty = Math.max(1, qty - 1)"
                    class="w-11 h-11 flex items-center justify-center text-gray-500 hover:bg-[#63A2BB]/10 hover:text-[#63A2BB] font-bold text-xl transition disabled:opacity-30 disabled:cursor-not-allowed"
                    :disabled="qty <= 1">
              −
            </button>
            <span x-text="qty" class="w-12 text-center font-bold text-gray-800 text-base"></span>
            <button type="button" @click="qty = Math.min(maxQty, qty + 1)"
                    class="w-11 h-11 flex items-center justify-center text-gray-500 hover:bg-[#63A2BB]/10 hover:text-[#63A2BB] font-bold text-xl transition disabled:opacity-30 disabled:cursor-not-allowed"
                    :disabled="qty >= maxQty">
              +
            </button>
          </div>
          <span class="text-sm text-gray-400">
            Maks <span x-text="maxQty" class="font-semibold"></span> pcs
          </span>
        </div>
      </div>

      {{-- Action Buttons --}}
      <div class="space-y-3 mt-6" x-data="{ tryonSupported: @js($isTryOnSupported) }">
        <button type="button"
                @click="window.dispatchEvent(new CustomEvent('open-tryon-modal'))"
                :disabled="!tryonSupported"
                :class="!tryonSupported ? 'opacity-50 cursor-not-allowed bg-slate-200 text-slate-500' : 'bg-gradient-to-r from-[#63A2BB] via-[#5293AC] to-[#4A8BA3] text-white hover:shadow-lg hover:shadow-[#63A2BB]/30 hover:-translate-y-0.5'"
                class="w-full py-3.5 px-6 rounded-2xl font-bold text-sm flex items-center justify-center gap-2 transition-all duration-200 shadow-md">
          <svg class="w-5 h-5 text-amber-300 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
          </svg>
          <span x-text="tryonSupported ? '✨ {{ __('ui.tryon_button') }}' : '{{ __('ui.tryon_unsupported') }}'"></span>
        </button>

        <div class="flex gap-3">
          <button type="button"
                  @click="addToCart()"
                  :disabled="!selectedDetail || loading"
                  :class="(!selectedDetail || loading)
                    ? 'opacity-60 cursor-not-allowed bg-[#63A2BB]'
                    : 'bg-[#63A2BB] hover:-translate-y-1 hover:shadow-lg hover:shadow-[#63A2BB]/30 hover:bg-[#4A8BA3]'"
                  class="flex-1 text-white py-4 px-6 rounded-2xl font-bold text-sm flex items-center justify-center gap-2 transition-all duration-200">
            <svg x-show="loading" class="animate-spin w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <svg x-show="!loading" class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <span x-text="loading ? 'Menambahkan...' : '{{ __('ui.add_to_cart') }}'"></span>
          </button>

          <button type="button"
                  @click="buyNow()"
                  :disabled="!selectedDetail || loading"
                  :class="(!selectedDetail || loading)
                    ? 'opacity-60 cursor-not-allowed'
                    : 'hover:-translate-y-1 hover:shadow-lg hover:bg-gray-800'"
                  class="flex-1 bg-gray-900 text-white py-4 px-6 rounded-2xl font-bold text-sm flex items-center justify-center gap-2 transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Beli Sekarang
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Product Tabs --}}
      <div class="mt-16 space-y-4">
        <details class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
          <summary class="flex cursor-pointer list-none items-center justify-between px-6 py-4 text-sm font-bold text-slate-900">
            <span>Deskripsi Produk</span>
            <i class="fas fa-chevron-down text-slate-400 transition-transform duration-200"></i>
          </summary>
          <div class="border-t border-slate-100 px-6 py-5 text-sm leading-7 text-slate-600">
            <div class="whitespace-pre-wrap">{!! nl2br(e($product->deskripsi ?? '')) !!}</div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2">
              <div class="rounded-2xl bg-slate-50 p-4">
                <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">Kode Produk</div>
                <div class="mt-1 font-semibold text-slate-900">{{ $product->kode_produk }}</div>
              </div>
              <div class="rounded-2xl bg-slate-50 p-4">
                <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">Kategori</div>
                <div class="mt-1 font-semibold text-slate-900">{{ $product->kategori->nama_kategori ?? '-' }}</div>
              </div>
              <div class="rounded-2xl bg-slate-50 p-4">
                <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">Gender</div>
                <div class="mt-1 font-semibold text-slate-900">{{ $product->gender ?? 'Unisex' }}</div>
              </div>
              <div class="rounded-2xl bg-slate-50 p-4">
                <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">Tipe Olahraga</div>
                <div class="mt-1 font-semibold text-slate-900">{{ $product->tipe_olahraga ?? '-' }}</div>
              </div>
            </div>
          </div>
        </details>

        <details class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm" open>
          <summary class="flex cursor-pointer list-none items-center justify-between px-6 py-4 text-sm font-bold text-slate-900">
            <span>Ulasan ({{ $product->ratings->count() }})</span>
            <i class="fas fa-chevron-down text-slate-400 transition-transform duration-200 rotate-180"></i>
          </summary>
          <div class="border-t border-slate-100 px-6 py-5">
            @include('components.product-reviews', [
              'ratingStats' => $ratingStats,
              'product' => $product,
              'hasPurchased' => $hasPurchased ?? false,
              'hasReviewed' => $hasReviewed ?? false
            ])
          </div>
        </details>
      </div>

  {{-- Similar Products --}}
  <div class="mt-16">
    <h2 class="text-2xl font-black text-gray-900 mb-8">
      Produk Serupa
    </h2>
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
      @forelse($similarProducts ?? [] as $item)
        <a href="{{ route('product.show', $item->slug) }}" class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
          <div class="aspect-square overflow-hidden bg-slate-50">
            <img src="{{ optional($item->gambarUtama)->url_lengkap ?? optional($item->gambarUtama)->url_safe ?? asset('images/placeholder.png') }}" alt="{{ $item->nama_produk }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
          </div>
          <div class="p-4">
            <div class="text-xs text-slate-500">{{ $item->kategori->nama_kategori ?? 'Item' }}</div>
            <div class="mt-1 truncate text-sm font-bold text-slate-900">{{ $item->nama_produk }}</div>
            <div class="mt-2 text-sm font-bold text-slate-900">Rp {{ number_format($item->harga_dasar ?? 0, 0, ',', '.') }}</div>
          </div>
        </a>
      @empty
        <div class="col-span-full text-center py-12 text-gray-500">
          Produk serupa tidak ditemukan
        </div>
      @endforelse
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
  window.productDetailState = function (variantItems, warnaOptions, hasWarna) {
    return {
      variantItems: variantItems,
      warnaOptions: warnaOptions,
      hasWarna: hasWarna,
      selectedWarna: null,
      selectedUkuran: null,
      selectedDetail: null,
      qty: 1,
      maxQty: 1,
      loading: false,

      init() {
        if (this.hasWarna && this.warnaOptions.length > 0) {
          this.selectedWarna = this.warnaOptions[0].warna_id;
          const variant = this.variantItems.find(item => item.warna_id === this.selectedWarna && item.gambar_url);
          if (variant && variant.gambar_url) {
            this.$nextTick(() => {
              this.$dispatch('change-active-image', { image: variant.gambar_url });
            });
          }
        } else {
          this.selectedWarna = '__no_color__';
        }

        this.$nextTick(() => {
          if (this.ukurans.length === 1) {
            this.selectUkuran(this.ukurans[0].detail_produk_id);
          }
        });
      },

      get warnas() {
        return this.warnaOptions;
      },

      get ukurans() {
        if (!this.hasWarna || this.selectedWarna === '__no_color__') return this.variantItems;
        if (!this.selectedWarna) return [];
        return this.variantItems.filter(item => item.warna_id === this.selectedWarna);
      },

      selectWarna(warnaId) {
        this.selectedWarna = warnaId;
        this.selectedUkuran = null;
        this.selectedDetail = null;
        this.qty = 1;
        this.maxQty = 1;

        const variant = this.variantItems.find(item => item.warna_id === warnaId && item.gambar_url);
        if (variant && variant.gambar_url) {
          this.$dispatch('change-active-image', { image: variant.gambar_url });
        }
      },

      selectUkuran(detailId) {
        this.selectedDetail = this.variantItems.find(item => item.detail_produk_id === detailId);
        this.selectedUkuran = this.selectedDetail && this.selectedDetail.ukuran ? this.selectedDetail.ukuran : null;
        this.maxQty = this.selectedDetail && this.selectedDetail.stok ? this.selectedDetail.stok : 1;
        this.qty = Math.max(1, Math.min(this.qty, this.maxQty || 1));
      },

      get selectedVariantText() {
        if (!this.selectedDetail) return 'Pilih ukuran';
        const parts = [];
        if (this.hasWarna && this.selectedDetail.nama_warna) {
          parts.push(this.selectedDetail.nama_warna);
        }
        if (this.selectedDetail.ukuran) {
          parts.push(this.selectedDetail.ukuran);
        }
        return parts.length ? parts.join(' / ') : 'Varian terpilih';
      },

      get totalStock() {
        return this.variantItems.reduce((sum, item) => sum + (Number(item.stok) || 0), 0);
      },

      async addToCart() {
        if (!this.selectedDetail) {
          showToast('Pilih ukuran dulu', 'warning');
          return false;
        }

        this.loading = true;
        try {
          const res = await fetch('{{ route('cart.add') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
              detail_produk_id: this.selectedDetail.detail_produk_id,
              jumlah: this.qty
            })
          });

          const data = await res.json();
          if (data.success) {
            showToast('Ditambahkan ke keranjang');
            window.dispatchEvent(new Event('cart-updated'));
            return true;
          }

          alert(data.message || 'Gagal ditambahkan');
          return false;
        } finally {
          this.loading = false;
        }
      },

      async buyNow() {
        if (!this.selectedDetail) {
          showToast('Pilih ukuran dulu', 'warning');
          return;
        }

        const added = await this.addToCart();
        if (added) {
          window.location.href = '{{ route('cart.index') }}';
        }
      }
    };
  };

    // Event listener untuk update cart badge
    window.addEventListener('cart-updated', () => {
        // Reload cart count badge di header
        fetch('/api/cart-count')
            .then(r => r.json())
            .then(data => {
                document.querySelector('[data-cart-badge]').textContent = data.count;
            });
    });
</script>

{{-- AI Virtual Try-On Modal Component --}}
<div x-data="tryOnModalState()"
     x-init="init()"
     @open-tryon-modal.window="openModal()"
     @keydown.escape.window="closeModal()"
     x-show="isOpen"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="tryon-modal-title"
     role="dialog"
     aria-modal="true">
  
  {{-- Overlay --}}
  <div x-show="isOpen"
       x-transition:enter="ease-out duration-300"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="ease-in duration-200"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       @click="closeModal()"
       class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

  {{-- Modal Dialog --}}
  <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
    <div x-show="isOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all w-full max-w-xl border border-slate-100">
      
      {{-- Modal Header --}}
      <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-xl bg-[#63A2BB]/10 text-[#63A2BB] flex items-center justify-center font-bold">
            ✨
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-900" id="tryon-modal-title">
              {{ __('ui.tryon_title') }}
            </h3>
            <p class="text-xs text-slate-500">{{ __('ui.tryon_subtitle') }}</p>
          </div>
        </div>
        <button @click="closeModal()" class="text-slate-400 hover:text-slate-600 p-2 rounded-xl hover:bg-slate-100 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      {{-- Stepper Progress Bar --}}
      <div class="px-6 pt-4 pb-2 border-b border-slate-100">
        <div class="flex items-center justify-between text-xs font-semibold text-slate-500 mb-2">
          <span :class="step >= 1 ? 'text-[#63A2BB] font-bold' : ''">1. {{ __('ui.tryon_step_1') }}</span>
          <span :class="step >= 2 ? 'text-[#63A2BB] font-bold' : ''">2. {{ __('ui.tryon_step_2') }}</span>
          <span :class="step >= 3 ? 'text-[#63A2BB] font-bold' : ''">3. {{ __('ui.tryon_step_3') }}</span>
          <span :class="step >= 4 ? 'text-[#63A2BB] font-bold' : ''">4. {{ __('ui.tryon_step_4') }}</span>
        </div>
        <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
          <div class="bg-[#63A2BB] h-full transition-all duration-300" :style="`width: ${step * 25}%`"></div>
        </div>
      </div>

      {{-- Modal Body --}}
      <div class="p-6">
        
        {{-- STEP 1: Upload Foto --}}
        <div x-show="step === 1" class="space-y-4">
          <div class="bg-amber-50 rounded-2xl p-4 border border-amber-200/60 text-xs text-amber-800 space-y-1">
            <p class="font-bold flex items-center gap-1.5 text-amber-900">
              <span>💡</span> {{ __('ui.tryon_guidelines_title') }}
            </p>
            <ul class="list-disc list-inside space-y-0.5 text-amber-800/90 pl-1">
              <li>{{ __('ui.tryon_guideline_1') }}</li>
              <li>{{ __('ui.tryon_guideline_2') }}</li>
              <li>{{ __('ui.tryon_guideline_3') }}</li>
            </ul>
          </div>

          {{-- Upload Drop Area --}}
          <div class="relative border-2 border-dashed border-slate-300 hover:border-[#63A2BB] rounded-2xl p-6 text-center transition-colors bg-slate-50/50"
               @dragover.prevent
               @drop.prevent="handleFileDrop($event)">
            
            <template x-if="!photoPreviewUrl">
              <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-[#63A2BB]/10 text-[#63A2BB] mx-auto flex items-center justify-center">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs font-semibold text-slate-700">{{ __('ui.tryon_upload_drag') }}</p>
                  <p class="text-[11px] text-slate-400 mt-0.5">JPG, PNG, WEBP (Maks 5 MB)</p>
                </div>
                <div class="flex items-center justify-center gap-2 pt-2">
                  <label class="cursor-pointer bg-[#63A2BB] hover:bg-[#4A8BA3] text-white text-xs font-bold py-2.5 px-4 rounded-xl transition-colors shadow-sm">
                    Pilih Foto
                    <input type="file" @change="selectFile($event.target.files[0])" accept="image/jpeg,image/png,image/webp" class="hidden">
                  </label>
                  <label class="cursor-pointer bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold py-2.5 px-4 rounded-xl transition-colors shadow-sm flex items-center gap-1.5">
                    <span>📷</span> {{ __('ui.tryon_upload_camera') }}
                    <input type="file" @change="selectFile($event.target.files[0])" accept="image/*" capture="environment" class="hidden">
                  </label>
                </div>
              </div>
            </template>

            <template x-if="photoPreviewUrl">
              <div class="relative inline-block group">
                <img :src="photoPreviewUrl" class="max-h-64 rounded-xl mx-auto object-cover border border-slate-200 shadow-md">
                <button @click="photoFile = null; photoPreviewUrl = null; sesiId = null"
                        class="absolute -top-2 -right-2 bg-red-500 text-white p-1.5 rounded-full shadow-lg hover:bg-red-600 transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </template>
          </div>

          {{-- Consent Checkbox --}}
          <label class="flex items-start gap-2.5 cursor-pointer bg-slate-50 p-3 rounded-xl border border-slate-200/80">
            <input type="checkbox" x-model="consent" class="mt-0.5 rounded text-[#63A2BB] focus:ring-[#63A2BB]">
            <span class="text-xs text-slate-600 leading-relaxed font-medium">
              {{ __('ui.tryon_consent_checkbox') }}
            </span>
          </label>

          {{-- Action Button --}}
          <div class="flex justify-end pt-2">
            <button @click="uploadPhoto()"
                    :disabled="!consent || (!photoFile && !sesiId) || isUploading"
                    class="bg-[#63A2BB] hover:bg-[#4A8BA3] text-white font-bold text-xs py-3 px-6 rounded-xl transition-all shadow-md disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
              <svg x-show="isUploading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <span>Lanjut Pilih Produk &rarr;</span>
            </button>
          </div>
        </div>

        {{-- STEP 2: Pilih Produk --}}
        <div x-show="step === 2" class="space-y-4">
          <div class="flex items-center justify-between">
            <h4 class="text-xs font-bold text-slate-800">Produk yang Dicoba:</h4>
            <span class="text-[11px] text-slate-400">Pilih dari katalog atau pencarian</span>
          </div>

          {{-- Selected Product Card --}}
          <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-2xl border border-[#63A2BB]/30">
            <img :src="selectedProduct.gambar_url" class="w-14 h-14 rounded-xl object-cover border border-slate-200">
            <div class="flex-1 min-w-0">
              <p class="text-xs font-bold text-slate-900 truncate" x-text="selectedProduct.nama_produk"></p>
              <p class="text-xs font-semibold text-[#63A2BB] mt-0.5">Rp <span x-text="selectedProduct.harga"></span></p>
            </div>
            <span class="bg-[#63A2BB]/10 text-[#63A2BB] text-[10px] font-bold px-2.5 py-1 rounded-full uppercase">Terpilih</span>
          </div>

          {{-- Search & Filter Section --}}
          <div class="space-y-2">
            <div class="relative">
              <input type="text"
                     x-model.debounce.300ms="searchQuery"
                     @input="searchProducts()"
                     placeholder="{{ __('ui.tryon_search_placeholder') }}"
                     class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-[#63A2BB]">
              <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </div>

            {{-- Category Filter Chips --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
              <button @click="categoryFilter = ''; searchProducts()"
                      :class="categoryFilter === '' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                      class="px-3 py-1 rounded-full font-medium transition-colors">
                {{ __('ui.tryon_filter_all') }}
              </button>
              <button @click="categoryFilter = 'top'; searchProducts()"
                      :class="categoryFilter === 'top' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                      class="px-3 py-1 rounded-full font-medium transition-colors">
                {{ __('ui.tryon_filter_top') }}
              </button>
              <button @click="categoryFilter = 'bottom'; searchProducts()"
                      :class="categoryFilter === 'bottom' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                      class="px-3 py-1 rounded-full font-medium transition-colors">
                {{ __('ui.tryon_filter_bottom') }}
              </button>
              <button @click="categoryFilter = 'dress'; searchProducts()"
                      :class="categoryFilter === 'dress' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                      class="px-3 py-1 rounded-full font-medium transition-colors">
                {{ __('ui.tryon_filter_dress') }}
              </button>
              <button @click="categoryFilter = 'outer'; searchProducts()"
                      :class="categoryFilter === 'outer' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                      class="px-3 py-1 rounded-full font-medium transition-colors">
                {{ __('ui.tryon_filter_outer') }}
              </button>
            </div>
          </div>

          {{-- Product Search Results Grid --}}
          <div x-show="searchResults.length > 0" class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto p-1">
            <template x-for="p in searchResults" :key="p.produk_id">
              <div @click="selectProductForTryOn(p)"
                   :class="selectedProduct.produk_id === p.produk_id ? 'border-[#63A2BB] bg-[#63A2BB]/5' : 'border-slate-200 hover:border-slate-300'"
                   class="flex items-center gap-2 p-2 rounded-xl border cursor-pointer transition-all">
                <img :src="p.gambar_url" class="w-10 h-10 rounded-lg object-cover">
                <div class="min-w-0 flex-1">
                  <p class="text-[11px] font-bold text-slate-800 truncate" x-text="p.nama_produk"></p>
                  <p class="text-[10px] text-[#63A2BB] font-semibold">Rp <span x-text="p.harga"></span></p>
                </div>
              </div>
            </template>
          </div>

          {{-- Action Buttons --}}
          <div class="flex items-center justify-between pt-2">
            <button @click="step = 1" class="text-xs font-bold text-slate-500 hover:text-slate-800">
              &larr; Kembali
            </button>
            <button @click="startGeneration()"
                    class="bg-[#63A2BB] hover:bg-[#4A8BA3] text-white font-bold text-xs py-3 px-6 rounded-xl transition-all shadow-md flex items-center gap-2">
              <span>✨ {{ __('ui.tryon_title') }}</span>
            </button>
          </div>
        </div>

        {{-- STEP 3: Loading Generating --}}
        <div x-show="step === 3" class="py-8 text-center space-y-6">
          <div class="relative w-20 h-20 mx-auto">
            <div class="absolute inset-0 rounded-full border-4 border-slate-100"></div>
            <div class="absolute inset-0 rounded-full border-4 border-[#63A2BB] border-t-transparent animate-spin"></div>
            <div class="absolute inset-0 flex items-center justify-center text-xl">✨</div>
          </div>

          <div class="space-y-2">
            <h4 class="text-sm font-bold text-slate-900">{{ __('ui.tryon_generating_title') }}</h4>
            <p class="text-xs text-slate-500 min-h-[20px]" x-text="generatingMessages[generatingMessageIndex]"></p>
          </div>

          {{-- Progress Bar --}}
          <div class="w-full max-w-xs mx-auto bg-slate-100 h-2 rounded-full overflow-hidden">
            <div class="bg-[#63A2BB] h-full transition-all duration-500" :style="`width: ${progressPercent}%`"></div>
          </div>
        </div>

        {{-- STEP 4: Hasil Try-On --}}
        <div x-show="step === 4" class="space-y-4">
          
          {{-- Before / After Interactive Visualizer --}}
          <div class="relative max-h-80 rounded-2xl overflow-hidden border border-slate-200 bg-slate-900 flex justify-center items-center">
            <template x-if="resultImageUrl">
              <img :src="resultImageUrl" class="max-h-80 w-auto object-contain mx-auto rounded-xl">
            </template>
          </div>

          {{-- Disclaimer Notice --}}
          <div class="bg-blue-50/70 p-3 rounded-xl border border-blue-200/60 text-[11px] text-blue-800 leading-relaxed">
            <span class="font-bold">ⓘ Disclaimer:</span> {{ __('ui.tryon_disclaimer') }}
          </div>

          {{-- Action Buttons --}}
          <div class="grid grid-cols-2 gap-2 pt-2">
            <button @click="step = 2" class="bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs py-2.5 px-3 rounded-xl transition-colors">
              {{ __('ui.tryon_btn_try_another') }}
            </button>
            <a :href="resultImageUrl" download="movr-tryon-result.jpg" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs py-2.5 px-3 rounded-xl text-center transition-colors">
              {{ __('ui.tryon_btn_download') }}
            </a>
            <button @click="deleteMyPhoto()" class="col-span-2 text-xs font-semibold text-red-500 hover:text-red-700 py-1 text-center">
              🗑️ {{ __('ui.tryon_btn_delete_photo') }}
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
function tryOnModalState() {
  return {
    isOpen: false,
    step: 1,
    consent: false,
    photoFile: null,
    photoPreviewUrl: null,
    sesiId: null,
    isUploading: false,
    isGenerating: false,
    statusPollInterval: null,
    progressPercent: 0,
    generatingMessageIndex: 0,
    generatingMessages: [
      "{{ __('ui.tryon_generating_text_1') }}",
      "{{ __('ui.tryon_generating_text_2') }}",
      "{{ __('ui.tryon_generating_text_3') }}"
    ],
    errorMessage: null,

    selectedProduct: {
      produk_id: {{ $product->produk_id }},
      nama_produk: @js($product->nama_produk),
      harga: @js(number_format($product->harga_dasar, 0, ',', '.')),
      gambar_url: @js($product->gambarProduk->first()?->url_lengkap ?? asset('images/placeholder.jpg')),
    },
    searchQuery: '',
    categoryFilter: '',
    searchResults: [],
    isSearching: false,
    resultImageUrl: null,

    init() {},

    openModal() {
      @if(!auth()->check())
        window.location.href = "{{ route('login') }}";
        return;
      @endif

      @if(!$isTryOnSupported)
        showToast("{{ __('ui.tryon_unsupported') }}", 'warning');
        return;
      @endif

      this.isOpen = true;
      if (!this.sesiId) {
        this.step = 1;
      }
    },

    closeModal() {
      this.isOpen = false;
      if (this.statusPollInterval) {
        clearInterval(this.statusPollInterval);
      }
    },

    handleFileDrop(e) {
      const files = e.dataTransfer ? e.dataTransfer.files : e.target.files;
      if (files && files.length > 0) {
        this.selectFile(files[0]);
      }
    },

    selectFile(file) {
      if (!file) return;
      if (!file.type.match(/^image\/(jpeg|jpg|png|webp)$/i)) {
        showToast("{{ __('ui.tryon_error_photo_format') }}", 'warning');
        return;
      }
      if (file.size > 5 * 1024 * 1024) {
        showToast("{{ __('ui.tryon_error_photo_size') }}", 'warning');
        return;
      }
      this.photoFile = file;
      this.photoPreviewUrl = URL.createObjectURL(file);
    },

    async uploadPhoto() {
      if (!this.consent) {
        showToast("{{ __('ui.tryon_error_consent_required') }}", 'warning');
        return;
      }
      if (!this.photoFile && !this.sesiId) {
        showToast("{{ __('ui.tryon_error_photo_required') }}", 'warning');
        return;
      }

      if (this.sesiId) {
        this.step = 2;
        return;
      }

      this.isUploading = true;
      this.errorMessage = null;

      const formData = new FormData();
      formData.append('foto', this.photoFile);
      formData.append('consent', '1');

      try {
        const res = await fetch("{{ route('tryon.upload') }}", {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: formData
        });

        const data = await res.json();
        if (res.ok && data.sesi_id) {
          this.sesiId = data.sesi_id;
          this.step = 2;
        } else {
          this.errorMessage = data.message || "Gagal mengunggah foto.";
          showToast(this.errorMessage, 'warning');
        }
      } catch (err) {
        this.errorMessage = "Terjadi kesalahan jaringan.";
        showToast(this.errorMessage, 'warning');
      } finally {
        this.isUploading = false;
      }
    },

    async searchProducts() {
      this.isSearching = true;
      try {
        const params = new URLSearchParams({
          q: this.searchQuery,
          category: this.categoryFilter
        });
        const res = await fetch(`{{ route('tryon.products') }}?${params.toString()}`, {
          headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        this.searchResults = data.data || [];
      } catch (e) {
        //
      } finally {
        this.isSearching = false;
      }
    },

    selectProductForTryOn(prod) {
      this.selectedProduct = prod;
    },

    async startGeneration() {
      if (!this.sesiId || !this.selectedProduct) return;

      this.step = 3;
      this.isGenerating = true;
      this.progressPercent = 10;
      this.errorMessage = null;

      try {
        const res = await fetch("{{ route('tryon.generate') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
            sesi_id: this.sesiId,
            produk_id: this.selectedProduct.produk_id
          })
        });

        if (!res.ok) {
          const errData = await res.json();
          this.errorMessage = errData.message || "Gagal memulai generasi Try-On.";
          this.step = 2;
          this.isGenerating = false;
          showToast(this.errorMessage, 'warning');
          return;
        }

        this.pollStatus();
      } catch (e) {
        this.errorMessage = "Gagal memulai proses.";
        this.step = 2;
        this.isGenerating = false;
      }
    },

    pollStatus() {
      let attempts = 0;
      const intervalMsg = setInterval(() => {
        this.generatingMessageIndex = (this.generatingMessageIndex + 1) % this.generatingMessages.length;
      }, 3000);

      this.statusPollInterval = setInterval(async () => {
        attempts++;
        this.progressPercent = Math.min(90, 10 + attempts * 15);

        try {
          const res = await fetch(`/tryon/status/${this.sesiId}`, {
            headers: { 'Accept': 'application/json' }
          });
          const data = await res.json();

          if (data.status === 'done') {
            clearInterval(this.statusPollInterval);
            clearInterval(intervalMsg);
            this.progressPercent = 100;
            this.resultImageUrl = `/tryon/result/${this.sesiId}?t=${Date.now()}`;
            this.isGenerating = false;
            this.step = 4;
          } else if (data.status === 'failed') {
            clearInterval(this.statusPollInterval);
            clearInterval(intervalMsg);
            this.isGenerating = false;
            this.errorMessage = data.pesan_error || "Proses Try-On gagal.";
            this.step = 2;
            showToast(this.errorMessage, 'warning');
          }
        } catch (e) {
          // Retry
        }

        if (attempts > 30) {
          clearInterval(this.statusPollInterval);
          clearInterval(intervalMsg);
          this.isGenerating = false;
          this.errorMessage = "Waktu tunggu habis. Silakan coba lagi.";
          this.step = 2;
        }
      }, 2500);
    },

    async deleteMyPhoto() {
      if (!confirm("{{ __('ui.tryon_confirm_delete_photo') }}")) return;

      if (this.sesiId) {
        fetch(`/tryon/photo/${this.sesiId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          }
        });
      }

      this.photoFile = null;
      this.photoPreviewUrl = null;
      this.sesiId = null;
      this.resultImageUrl = null;
      this.step = 1;
      showToast("Foto Anda telah dihapus.");
    }
  };
}
</script>
@endsection
