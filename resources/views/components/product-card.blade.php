@props([
    'produk' => null,
    'showWishlistBtn' => true,
    'badge' => null,
    'promo' => null,
    'image' => null,
    'title' => null,
    'price' => 0,
    'category' => null,
    'rating' => 5.0,
    'productId' => null,
    'detailId' => null,
    'detailUrl' => null,
    'addCartUrl' => null
])

@php
    use App\Models\Wishlist;

    $isLoggedIn = auth()->check();
    $wishlistOwnerColumn = Wishlist::ownerColumn();
    $wishlistOwnerId = $isLoggedIn ? Wishlist::resolveOwnerId(auth()->user()) : null;

    $pId = $produk->produk_id ?? $productId ?? 0;
    $pSlug = $produk->slug ?? null;
    $pName = $produk->nama_produk ?? $title ?? 'Produk';
    $pPrice = (float)($produk->harga_dasar ?? $price ?? 0);
    $pCat = $produk->kategori->nama_kategori ?? $category ?? null;

    $isInWishlist = ($isLoggedIn && $wishlistOwnerId && $pId)
        ? Wishlist::where($wishlistOwnerColumn, $wishlistOwnerId)->where('produk_id', $pId)->exists()
        : false;

    $avgRating = $produk->rata_rating ?? (isset($produk->ratings) ? ($produk->ratings->avg('bintang') ?? 0) : ($rating ?? 5.0));
    $reviewCount = $produk->jumlah_ulasan ?? (isset($produk->ratings) ? $produk->ratings->count() : 0);

    $promoDiscount = 0;
    if (! empty($promo)) {
        $promoDiscount = (float) ($promo->diskon ?? $promo->nominal_diskon ?? 0);
        if ($promoDiscount <= 0 && ! empty($promo->persen_diskon)) {
            $promoDiscount = $pPrice * ((float) $promo->persen_diskon) / 100;
        }
    }

    $imageSource = $image ?? (optional($produk?->images?->first())->url_lengkap
        ?? (optional($produk?->images?->first())->url_gambar ? asset('storage/' . $produk->images->first()->url_gambar) : asset('images/default-product.svg')));
    
    $dId = $detailId ?? optional($produk?->details?->first())->detail_produk_id;
    $finalPrice = $promoDiscount > 0 ? max(0, $pPrice - $promoDiscount) : $pPrice;
    $productUrl = $pSlug ? route('product.show', $pSlug) : ($detailUrl ?? '#');
@endphp

<div class="group flex h-full flex-col overflow-hidden rounded-[2rem] border border-slate-200/50 bg-white shadow-[0_8px_30px_rgba(0,0,0,0.02)] transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_20px_40px_rgba(99,162,187,0.12)]" 
     x-data="{ 
        isWishlisted: {{ $isInWishlist ? 'true' : 'false' }}, 
        loading: false, 
        async toggleWishlist() { 
            if (!{{ $isLoggedIn ? 'true' : 'false' }}) { window.location.href = '{{ route('login') }}'; return; } 
            this.loading = true; 
            try { 
                const res = await fetch('{{ route('wishlist.toggle') }}', { 
                    method: 'POST', 
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'), 
                        'Accept': 'application/json' 
                    }, 
                    body: JSON.stringify({ produk_id: {{ $pId }} }) 
                }); 
                const data = await res.json(); 
                if (data.success) { 
                    const wasWishlisted = this.isWishlisted; 
                    this.isWishlisted = !this.isWishlisted; 
                    window.dispatchEvent(new Event('wishlist-updated')); 
                    if (wasWishlisted && !this.isWishlisted) { 
                        window.dispatchEvent(new CustomEvent('wishlist-removed', { detail: { produk_id: {{ $pId }} } })); 
                    } 
                    if (typeof showToast === 'function') { 
                        showToast(this.isWishlisted ? '{{ __('ui.product_added_wishlist') }}' : '{{ __('ui.product_removed_wishlist') }}', this.isWishlisted ? 'success' : 'info'); 
                    } 
                } else if (data.message && typeof showToast === 'function') { 
                    showToast(data.message, 'error'); 
                } 
            } catch (error) { 
                console.error(error); 
                if (typeof showToast === 'function') { 
                    showToast('{{ __('ui.wishlist_update_failed') }}', 'error'); 
                } 
            } finally { 
                this.loading = false; 
            } 
        }, 
        async addToCart() { 
            if (!{{ $isLoggedIn ? 'true' : 'false' }}) { window.location.href = '{{ route('login') }}'; return; } 
            const detailId = {{ $dId ? (int) $dId : 'null' }}; 
            if (!detailId) { 
                if (typeof showToast === 'function') { 
                    showToast('{{ __('ui.product_variant_unavailable') }}', 'warning'); 
                } 
                return; 
            } 
            try { 
                const fd = new FormData(); 
                fd.append('detail_produk_id', detailId); 
                fd.append('jumlah', 1); 
                const res = await fetch('{{ route('cart.add') }}', { 
                    method: 'POST', 
                    headers: { 
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'), 
                        'Accept': 'application/json' 
                    }, 
                    body: fd 
                }); 
                const data = await res.json(); 
                if (data.success) { 
                    window.dispatchEvent(new Event('cart-updated')); 
                    if (typeof showToast === 'function') { 
                        showToast(data.message || '{{ __('ui.product_added_cart') }}', 'success'); 
                    } 
                } else if (typeof showToast === 'function') { 
                    showToast(data.message || '{{ __('ui.product_add_cart_failed') }}', 'error'); 
                } 
            } catch (error) { 
                console.error(error); 
                if (typeof showToast === 'function') { 
                    showToast('{{ __('ui.product_add_cart_failed') }}', 'error'); 
                } 
            } 
        } 
     }">
    
    <div class="relative aspect-[3/4] overflow-hidden bg-slate-50">
        <a href="{{ $productUrl }}" class="block h-full w-full">
            <img src="{{ $imageSource }}" alt="{{ $pName }}" class="h-full w-full object-cover transition-transform duration-500 ease-in-out group-hover:scale-105" onerror="this.src='{{ asset('images/default-product.svg') }}';">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/10 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
        </a>

        @if(!empty($badge))
            <span class="absolute left-4 top-4 rounded-full bg-[#63A2BB] px-3.5 py-1 text-[9px] font-black uppercase tracking-[0.22em] text-white shadow-md shadow-[#63A2BB]/20">
                {{ $badge }}
            </span>
        @endif

        @if($showWishlistBtn)
            <button type="button" @click.stop="toggleWishlist()" :disabled="loading" class="absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/90 backdrop-blur-md text-slate-700 shadow-md transition-all duration-300 hover:scale-110 hover:bg-white hover:text-red-500">
                <svg x-show="!isWishlisted" class="h-5 w-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 10-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <svg x-cloak x-show="isWishlisted" class="h-5 w-5 text-red-500 fill-red-500" viewBox="0 0 24 24">
                    <path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402 0-3.791 3.068-5.191 5.281-5.191 1.312 0 4.151.501 5.719 4.457 1.59-3.968 4.464-4.447 5.726-4.447 2.54 0 5.274 1.621 5.274 5.181 0 4.069-5.136 8.625-11 14.402z" />
                </svg>
            </button>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-5 sm:p-6 bg-white">
        @if(!empty($pCat))
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-[#63A2BB]/85 mb-1.5 block">
                {{ $pCat }}
            </span>
        @endif

        <a href="{{ $productUrl }}" class="line-clamp-2 text-sm font-extrabold text-slate-800 transition-colors duration-200 hover:text-[#63A2BB] leading-snug min-h-[2.5rem]">
            {{ $pName }}
        </a>

        <div class="mt-2.5 flex items-center gap-2 text-[11px] text-slate-400 font-semibold">
            <div class="flex items-center gap-0.5 text-amber-400">
                @for($i = 1; $i <= 5; $i++)
                    <svg class="h-3 w-3 {{ $i <= round($avgRating) ? 'fill-current text-amber-400' : 'fill-slate-200 text-slate-200' }}" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                @endfor
            </div>
            <span>({{ round($avgRating, 1) }})</span>
            <span>•</span>
            <span>{{ $reviewCount }} {{ __('ui.review_count') }}</span>
        </div>

        <div class="mt-4 flex items-end justify-between gap-3">
            <div>
                @if($promoDiscount > 0)
                    <div class="text-[10px] font-bold text-slate-400 line-through tracking-wider">Rp {{ number_format((float) $pPrice, 0, ',', '.') }}</div>
                @endif
                <div class="text-base font-black text-slate-900">Rp {{ number_format((float) $finalPrice, 0, ',', '.') }}</div>
            </div>
            @if(isset($produk->total_terjual) && $produk->total_terjual > 0)
                <div class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full uppercase tracking-wider">
                    {{ number_format((int) $produk->total_terjual) }} {{ __('ui.products_sold') }}
                </div>
            @endif
        </div>

        <div class="mt-5">
            <a href="{{ $productUrl }}" class="inline-flex items-center justify-center w-full rounded-full border border-slate-200 bg-white py-3 text-xs font-bold text-slate-800 transition-all duration-300 hover:border-[#63A2BB] hover:bg-[#63A2BB] hover:text-white shadow-sm">
                {{ __('ui.view_detail') }}
            </a>
        </div>
    </div>
</div>
