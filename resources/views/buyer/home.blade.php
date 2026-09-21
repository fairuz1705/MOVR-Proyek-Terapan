@extends('layouts.buyer')

@section('title', 'Home - MOVR')

@section('content')
@php
    $heroProduct = $newArrivals->first();
    $heroImage = optional($heroProduct?->gambarUtama)->url_lengkap
        ?? optional($heroProduct?->gambarUtama)->url_safe
        ?? asset('images/default-banner.svg');

    $tickerItems = ['Running', 'Basketball', 'Football', 'Gym', 'Tennis', 'Cycling', 'Swimming', 'Yoga', 'Boxing', 'Training'];
    $sportCards = [
        [
            'image' => 'https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?auto=format&fit=crop&w=600&q=80', 
            'title' => 'Running', 
            'subtitle' => 'Shoes, apparel, and endurance gear'
        ],
        [
            'image' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=600&q=80', 
            'title' => 'Gym & Training', 
            'subtitle' => 'Strength essentials and activewear'
        ],
        [
            'image' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?auto=format&fit=crop&w=600&q=80', 
            'title' => 'Football', 
            'subtitle' => 'Kits, boots, and match-day equipment'
        ],
        [
            'image' => 'https://images.unsplash.com/photo-1595435934249-5df7ed86e1c0?auto=format&fit=crop&w=600&q=80', 
            'title' => 'Racket Sports', 
            'subtitle' => 'Fast play gear for tennis and more'
        ],
    ];
    $featureStats = [
        ['value' => '2K+', 'label' => 'Products curated'],
        ['value' => '15+', 'label' => 'Sport categories'],
        ['value' => '98%', 'label' => 'Satisfaction rate'],
    ];
@endphp

<style>
    @keyframes movrTicker {
        from { transform: translateX(0); }
        to { transform: translateX(-50%); }
    }

    @keyframes movrFloat {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    .movr-ticker-track {
        animation: movrTicker 22s linear infinite;
    }

    .movr-float {
        animation: movrFloat 4s ease-in-out infinite;
    }

    .scrollbar-none::-webkit-scrollbar {
        display: none;
    }

    .scrollbar-none {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

{{-- Full-Width Hero Section (Directly attached to promo banner above, zero top margin) --}}
<section class="w-full relative overflow-hidden bg-[#0A1020] text-white shadow-2xl border-b border-slate-800">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(99,162,187,0.36),transparent_34%),linear-gradient(125deg,rgba(10,16,32,0.96),rgba(13,23,44,0.92))]"></div>
    <div class="absolute -right-12 top-12 h-56 w-56 rounded-full border border-white/10"></div>
    <div class="absolute -left-16 bottom-0 h-72 w-72 rounded-full border border-[#63A2BB]/15"></div>

    <div class="relative max-w-7xl mx-auto grid gap-10 px-4 sm:px-6 md:px-12 py-10 md:grid-cols-2 md:py-16 lg:px-8 lg:py-20">
        <div class="space-y-6">
            <div class="inline-flex items-center gap-3 rounded-full border border-[#63A2BB]/30 bg-[#63A2BB]/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.28em] text-[#8FD0E3]">
                <span class="h-2 w-2 rounded-full bg-[#63A2BB] shadow-[0_0_0_6px_rgba(99,162,187,0.18)]"></span>
                New Drop 2026
            </div>

            <div class="space-y-4">
                <p class="text-sm font-semibold uppercase tracking-[0.32em] text-white/55">PUSH YOUR LIMITS</p>
                <h1 class="max-w-xl text-4xl font-black leading-[0.9] tracking-tight sm:text-5xl lg:text-7xl">
                    <span class="block">PUSH</span>
                    <span class="block text-transparent [-webkit-text-stroke:2px_#63A2BB]">YOUR</span>
                    <span class="block text-[#63A2BB]">LIMITS</span>
                </h1>
                <p class="max-w-xl text-sm leading-7 text-white/70 sm:text-base">
                    Premium sportswear, footwear, and gear untuk latihan, kompetisi, dan lifestyle aktif yang bergerak cepat.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('product.index') }}" class="inline-flex items-center justify-center rounded-full bg-[#63A2BB] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-[#63A2BB]/30 transition hover:-translate-y-0.5 hover:bg-[#4e8fa8]">
                    Shop Now
                    <span class="ml-1">→</span>
                </a>
                <a href="#shop-by-sport" class="inline-flex items-center justify-center rounded-full border border-white/20 bg-white/5 px-6 py-3 text-sm font-bold text-white transition hover:border-[#63A2BB]/40 hover:bg-white/10">
                    Explore Sports
                </a>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                @foreach($featureStats as $stat)
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4 backdrop-blur-sm">
                        <div class="text-2xl font-black text-white">{{ $stat['value'] }}</div>
                        <div class="mt-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="relative flex items-center justify-center">
            <div class="movr-float relative w-full max-w-sm rounded-[2rem] border border-white/10 bg-white/8 p-4 backdrop-blur-xl">
                <div class="absolute -left-4 top-8 rounded-2xl bg-white px-4 py-3 text-slate-900 shadow-2xl">
                    <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#63A2BB]">Top Pick</div>
                    <div class="mt-1 text-sm font-black">Sports Ready</div>
                </div>

                <div class="overflow-hidden rounded-[1.5rem] bg-slate-900/40">
                    <img src="{{ $heroImage }}" alt="Hero product" class="h-[350px] w-full object-cover object-center">
                </div>

                <div class="mt-4 rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-white/40">Featured product</div>
                            <div class="mt-1 text-base font-bold text-white">{{ $heroProduct->nama_produk ?? 'Training Essential' }}</div>
                            <div class="text-sm text-white/55">{{ $heroProduct->tipe_olahraga ?? 'Performance gear for every session' }}</div>
                        </div>
                        <div class="rounded-2xl bg-[#63A2BB] px-4 py-3 text-right text-white">
                            <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-white/80">From</div>
                            <div class="text-lg font-black">Rp {{ number_format($heroProduct->harga_dasar ?? 349000, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="absolute -bottom-5 right-6 rounded-2xl border border-white/10 bg-white px-4 py-3 text-slate-900 shadow-2xl shadow-black/20">
                <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#63A2BB]">4.9 / 5.0</div>
                <div class="text-sm font-semibold">Rated by athletes</div>
            </div>
        </div>
    </div>
</section>

{{-- Ticker Bar (Directly attached to Hero section, zero gap) --}}
<section class="w-full overflow-hidden border-b border-[#63A2BB]/15 bg-[#63A2BB] py-4 text-white">
    <div class="movr-ticker-track flex w-max items-center gap-10 whitespace-nowrap px-4 text-xs font-black uppercase tracking-[0.35em] sm:text-sm">
        @foreach(array_merge($tickerItems, $tickerItems) as $tickerItem)
            <span class="inline-flex items-center gap-3">
                <span class="h-1.5 w-1.5 rounded-full bg-white/70"></span>
                {{ $tickerItem }}
            </span>
        @endforeach
    </div>
</section>

<div class="space-y-16 py-12">
    {{-- Flash Sale Section --}}
    @if(isset($flashProducts) && $flashProducts->isNotEmpty())
        <section class="section-shell space-y-6" x-data="countdown('{{ $flashEndTime }}')" x-init="init()">
            <div class="rounded-[2rem] border border-[#FF4E4E]/30 bg-gradient-to-br from-[#1C0D0D] to-[#0A0505] p-6 sm:p-8 lg:p-10 shadow-xl shadow-red-950/20 relative overflow-hidden">
                {{-- Decorative background glow --}}
                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-red-600/15 blur-3xl pointer-events-none"></div>
                <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-amber-500/10 blur-3xl pointer-events-none"></div>

                <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-white/10">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 rounded-full border border-red-500/40 bg-red-500/10 px-3 py-1 text-xs font-bold uppercase tracking-widest text-red-400">
                            <span class="h-2 w-2 rounded-full bg-red-500 animate-pulse"></span>
                            Live Now
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-3">
                            <span>⚡</span> FLASH SALE
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-400">Penawaran terbatas dengan harga terendah musim ini.</p>
                    </div>

                    {{-- Countdown timer --}}
                    <div class="flex items-center gap-2 text-white">
                        <div class="text-xs uppercase font-bold text-slate-400 mr-2 hidden sm:block">Ends in</div>
                        <div class="flex gap-2 text-center font-mono">
                            <div class="rounded-xl border border-white/15 bg-white/5 px-3 py-2 min-w-[50px]">
                                <div class="text-lg sm:text-xl font-black text-red-400" x-text="hours">00</div>
                                <div class="text-[9px] uppercase tracking-wider text-slate-400">Hours</div>
                            </div>
                            <div class="text-xl font-bold self-center text-slate-500">:</div>
                            <div class="rounded-xl border border-white/15 bg-white/5 px-3 py-2 min-w-[50px]">
                                <div class="text-lg sm:text-xl font-black text-red-400" x-text="minutes">00</div>
                                <div class="text-[9px] uppercase tracking-wider text-slate-400">Mins</div>
                            </div>
                            <div class="text-xl font-bold self-center text-slate-500">:</div>
                            <div class="rounded-xl border border-white/15 bg-white/5 px-3 py-2 min-w-[50px]">
                                <div class="text-lg sm:text-xl font-black text-red-400" x-text="seconds">00</div>
                                <div class="text-[9px] uppercase tracking-wider text-slate-400">Secs</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Product cards slider/grid --}}
                <div class="mt-8 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($flashProducts as $item)
                        @php
                            $p = $item->produk;
                            $pr = $item->promo;
                            $img = optional($p->gambarUtama)->url_lengkap
                                ?? optional($p->gambarUtama)->url_safe
                                ?? asset('images/default-product.svg');
                            $hargaDasar = (float) $p->harga_dasar;
                            $diskon = (float) $pr->diskon;
                            $hargaAkhir = max(0, $hargaDasar - $diskon);
                            $persenDiskon = $hargaDasar > 0 ? round(($diskon / $hargaDasar) * 100) : 0;
                            if (!empty($pr->persen_diskon) && $pr->persen_diskon > 0) {
                                $persenDiskon = (int) $pr->persen_diskon;
                            }
                        @endphp
                        <div class="group relative rounded-2xl border border-white/10 bg-white/5 p-3 sm:p-4 transition hover:-translate-y-1 hover:border-red-500/50 hover:bg-white/10 flex flex-col justify-between">
                            <div>
                                <div class="relative overflow-hidden rounded-xl bg-slate-900/60 aspect-square">
                                    <img src="{{ $img }}" alt="{{ $p->nama_produk }}" class="h-full w-full object-cover object-center group-hover:scale-105 transition duration-300">
                                    <div class="absolute top-2 left-2 rounded-lg bg-red-600 px-2 py-1 text-[10px] font-black uppercase text-white shadow-md">
                                        -{{ $persenDiskon }}%
                                    </div>
                                </div>
                                <div class="mt-3 space-y-1">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 line-clamp-1">
                                        {{ $p->kategori->nama_kategori ?? 'Sports' }}
                                    </div>
                                    <h3 class="text-xs sm:text-sm font-bold text-white line-clamp-1 group-hover:text-red-400 transition">
                                        <a href="{{ route('product.show', $p->slug) }}">{{ $p->nama_produk }}</a>
                                    </h3>
                                </div>
                            </div>
                            <div class="mt-3 pt-2 border-t border-white/10">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-sm sm:text-base font-black text-red-400">Rp {{ number_format($hargaAkhir, 0, ',', '.') }}</span>
                                    <span class="text-[10px] sm:text-xs text-slate-500 line-through">Rp {{ number_format($hargaDasar, 0, ',', '.') }}</span>
                                </div>
                                <a href="{{ route('product.show', $p->slug) }}" class="mt-3 block w-full rounded-xl bg-red-600 py-2 text-center text-xs font-bold text-white shadow-lg hover:bg-red-500 transition">
                                    Beli Sekarang
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Recommended Products (Horizontal Carousel) --}}
    @if(isset($recommendedProducts) && $recommendedProducts->isNotEmpty())
        <section class="section-shell space-y-6">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.28em] text-[#63A2BB]">Rekomendasi Untuk Anda</p>
                    <h2 class="mt-1 text-2xl sm:text-3xl font-black text-slate-900">Recommended Products</h2>
                </div>
                <span class="text-xs text-slate-400 font-semibold hidden sm:inline-flex items-center gap-1.5 bg-slate-100 px-3 py-1.5 rounded-full">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    Swipe ke kanan / kiri
                </span>
            </div>

            <div class="flex overflow-x-auto pb-4 gap-6 scroll-smooth snap-x snap-mandatory scrollbar-none" style="-webkit-overflow-scrolling: touch;">
                @foreach($recommendedProducts as $product)
                    <div class="w-[260px] sm:w-[280px] flex-shrink-0 snap-start">
                        <x-product-card :produk="$product" :badge="'FOR YOU'" :showWishlistBtn="true" />
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Shop by Sport --}}
    <section id="shop-by-sport" class="section-shell space-y-6">
        <div class="flex items-end justify-between border-b border-slate-200 pb-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#63A2BB]">Gear Up</p>
                <h2 class="text-2xl font-black text-slate-900 sm:text-3xl">Shop by Sport</h2>
            </div>
            <a href="{{ route('product.index') }}" class="text-xs font-bold uppercase tracking-wider text-[#63A2BB] hover:underline">View All Sports →</a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($sportCards as $card)
                <div class="group relative overflow-hidden rounded-[1.5rem] bg-slate-900 text-white shadow-md">
                    <img src="{{ $card['image'] }}" alt="{{ $card['title'] }}" class="h-64 w-full object-cover opacity-70 transition duration-500 group-hover:scale-105 group-hover:opacity-50">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
                    <div class="absolute bottom-0 p-6">
                        <h3 class="text-xl font-bold">{{ $card['title'] }}</h3>
                        <p class="mt-1 text-xs text-slate-300">{{ $card['subtitle'] }}</p>
                        <a href="{{ route('product.index') }}" class="mt-4 inline-flex items-center text-xs font-bold text-[#63A2BB] group-hover:text-white">
                            Explore Collection <span class="ml-1">→</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Best Sellers --}}
    @if(isset($bestSellers) && $bestSellers->isNotEmpty())
        <section class="section-shell space-y-6">
            <div class="flex items-end justify-between border-b border-slate-200 pb-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#63A2BB]">Paling Populer</p>
                    <h2 class="text-2xl font-black text-slate-900 sm:text-3xl">Best Sellers</h2>
                </div>
                <a href="{{ route('product.index') }}?sort=terlaris" class="text-xs font-bold uppercase tracking-wider text-[#63A2BB] hover:underline">View All →</a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($bestSellers as $product)
                    <x-product-card :produk="$product" :badge="'BESTSELLER'" :showWishlistBtn="true" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- New Arrivals --}}
    @if($newArrivals->isNotEmpty())
        <section class="section-shell space-y-6">
            <div class="flex items-end justify-between border-b border-slate-200 pb-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#63A2BB]">Fresh Drops</p>
                    <h2 class="text-2xl font-black text-slate-900 sm:text-3xl">New Arrivals</h2>
                </div>
                <a href="{{ route('product.index') }}?sort=terbaru" class="text-xs font-bold uppercase tracking-wider text-[#63A2BB] hover:underline">See All Products →</a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($newArrivals as $product)
                    <x-product-card :produk="$product" :badge="'NEW'" :showWishlistBtn="true" />
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function countdown(endTimeStr) {
        return {
            endTime: endTimeStr ? new Date(endTimeStr).getTime() : 0,
            hours: '00',
            minutes: '00',
            seconds: '00',
            timer: null,
            init() {
                if (!this.endTime) return;
                this.update();
                this.timer = setInterval(() => {
                    this.update();
                }, 1000);
            },
            update() {
                const now = new Date().getTime();
                const diff = this.endTime - now;
                if (diff <= 0) {
                    clearInterval(this.timer);
                    this.hours = '00';
                    this.minutes = '00';
                    this.seconds = '00';
                    return;
                }
                const h = Math.floor(diff / (1000 * 60 * 60));
                const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((diff % (1000 * 60)) / 1000);
                this.hours = String(h).padStart(2, '0');
                this.minutes = String(m).padStart(2, '0');
                this.seconds = String(s).padStart(2, '0');
            }
        }
    }
</script>
@endsection
