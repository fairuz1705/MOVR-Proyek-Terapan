{{--
  // ── FILE: resources/views/layouts/app.blade.php ──
  Header + Footer wrapper sesuai spec MOVR (Tailwind + Alpine).
--}}
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MOVR')</title>

    {{-- Inter Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Tailwind CDN (Laravel + Vite juga bisa, tapi requirement spec pakai modern UI) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        html { font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji"; }
        :root {
            --brand: #63a2bb;
            --brand-strong: #4f90aa;
            --brand-soft: #eef6fa;
        }
        body {
            background: radial-gradient(circle at top right, rgba(99,162,187,0.18), transparent 35%), #ffffff;
            color: #1e293b;
        }

        /* Global remap for existing dark utility classes in page content */
        main [class*="bg-white/5"] { background-color: #ffffff !important; }
        main [class*="bg-white/10"] { background-color: #f8fcff !important; }
        main [class*="bg-black/20"],
        main [class*="bg-black/30"],
        main [class*="bg-black/40"] { background-color: var(--brand-soft) !important; }
        main [class*="border-white/10"],
        main [class*="border-white/15"] { border-color: rgba(99,162,187,0.28) !important; }
        main [class*="text-slate-300"] { color: #475569 !important; }
        main [class*="text-cyan-300"],
        main [class*="text-cyan-400"],
        main [class*="text-cyan-500"] { color: var(--brand-strong) !important; }
        main [class*="bg-cyan-500"] { background-color: var(--brand) !important; color: #ffffff !important; }
        main [class*="hover:bg-cyan-400"]:hover { background-color: var(--brand-strong) !important; }
        main [class*="border-cyan-400"] { border-color: var(--brand) !important; }
        main [class*="accent-cyan-400"] { accent-color: var(--brand) !important; }
        main [class*="focus:border-cyan-400"]:focus { border-color: var(--brand) !important; }
    </style>
</head>
<body class="min-h-screen">

    @php
        use App\Models\Wishlist;
        use App\Models\Keranjang;
        $isLoggedIn = auth()->check();
        $wishlistOwnerColumn = Wishlist::ownerColumn();
        $wishlistOwnerId = $isLoggedIn ? Wishlist::resolveOwnerId(auth()->user()) : null;
        $wishlistCount = $wishlistOwnerId ? Wishlist::where($wishlistOwnerColumn, $wishlistOwnerId)->count() : 0;

        $cartOwnerColumn = Keranjang::ownerColumn();
        $cartOwnerId = $isLoggedIn ? Keranjang::resolveOwnerId(auth()->user()) : null;
        $cartCount = $cartOwnerId ? Keranjang::where($cartOwnerColumn, $cartOwnerId)->distinct()->count('detail_produk_id') : 0;
    @endphp

    <div x-data="movrHeader()" x-init="init()" class="bg-transparent text-slate-800">
        {{-- Mobile Drawer --}}
        <div class="fixed inset-0 z-50" aria-hidden="true" x-show="drawerOpen" x-cloak>
            <div class="absolute inset-0 bg-[#63a2bb]/30" @click="drawerOpen=false"></div>
            <div class="relative h-full w-80 max-w-[85vw] bg-white border-r border-[#63a2bb]/30 p-4 overflow-y-auto shadow-xl">
                <div class="flex items-center justify-between">
                    <a href="{{ route('home') }}" class="text-xl font-black tracking-wide text-[#63a2bb] no-underline">MOVR</a>
                    <button type="button" class="rounded-full border border-[#63a2bb]/35 px-3 py-1 text-sm text-slate-700 hover:bg-[#63a2bb]/10" @click="drawerOpen=false">{{ __('ui.close') }}</button>
                </div>

                <div class="mt-4">
                    <div class="text-sm font-semibold text-slate-700 mb-2">{{ __('ui.mega_menu') }}</div>
                    <div class="space-y-2">
                        @php
                            $level1 = \App\Models\Kategori::whereNull('parent_id')->where('level',1)->orderBy('urutan')->get();
                        @endphp
                        @foreach($level1 as $l1)
                            <a href="{{ route('category.show', $l1->slug) }}" class="block rounded-xl border border-[#63a2bb]/25 px-3 py-2 hover:bg-[#63a2bb]/10">
                                {{ $l1->nama_kategori }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="mt-5">
                    <div class="text-sm font-semibold text-slate-700 mb-2">{{ __('ui.search') }}</div>
                    <form method="get" action="{{ url('/search') }}" class="flex gap-2">
                        <input class="w-full rounded-xl border border-[#63a2bb]/25 bg-white px-3 py-2 text-sm outline-none focus:border-[#63a2bb]" placeholder="{{ __('ui.search_products') }}" name="q" />
                        <button class="rounded-xl bg-[#63a2bb] px-4 py-2 text-sm font-semibold text-white hover:bg-[#4f90aa]" type="submit">{{ __('ui.search') }}</button>
                    </form>
                </div>

                <div class="mt-5">
                    <div class="flex items-center gap-3">
                        <a class="rounded-full border border-[#63a2bb]/35 px-3 py-2 text-sm hover:bg-[#63a2bb]/10" href="{{ route('wishlist.index') }}">{{ __('ui.wishlist') }}</a>
                        <a class="rounded-full border border-[#63a2bb]/35 px-3 py-2 text-sm hover:bg-[#63a2bb]/10" href="{{ route('cart.index') }}">{{ __('ui.cart') }}</a>
                    </div>
                </div>

                <div class="mt-5">
                    @guest
                        <a class="block rounded-xl border border-[#63a2bb]/25 px-3 py-2 text-sm hover:bg-[#63a2bb]/10" href="{{ route('login') }}">{{ __('ui.login') }}</a>
                        <a class="block rounded-xl border border-[#63a2bb]/25 px-3 py-2 text-sm mt-2 hover:bg-[#63a2bb]/10" href="{{ route('register') }}">{{ __('ui.register') }}</a>
                    @else
                        <a class="block rounded-xl border border-[#63a2bb]/25 px-3 py-2 text-sm hover:bg-[#63a2bb]/10" href="{{ route('profile.index') }}">{{ __('ui.profile') }}</a>
                        <form method="post" action="{{ route('logout') }}" class="mt-2">
                            @csrf
                            <button type="submit" class="w-full rounded-xl border border-[#63a2bb]/25 px-3 py-2 text-sm hover:bg-[#63a2bb]/10">Logout</button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>

        {{-- Header --}}
        <header class="sticky top-0 z-40 border-b border-[#63a2bb]/30 bg-white/95 backdrop-blur">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        {{-- Logo --}}
                        <a href="{{ route('home') }}" class="text-xl font-black tracking-wide text-[#63a2bb] no-underline">MOVR</a>

                        {{-- Desktop Mega Menu --}}
                        <div class="hidden md:block">
                            <div class="relative" @mouseleave="categoryHover=false">
                                <button type="button" class="rounded-full border border-[#63a2bb]/35 bg-[#63a2bb]/10 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-[#63a2bb]/20" @mouseenter="categoryHover=true" @click.prevent>
                                    {{ __('ui.categories') }}
                                </button>

                                <div class="absolute left-0 mt-3 w-[560px] bg-white border border-[#63a2bb]/25 rounded-2xl p-4 shadow-xl" x-show="categoryHover" x-cloak>
                                    <div class="grid grid-cols-3 gap-4">
                                        @php
                                            $l1s = \App\Models\Kategori::where('level',1)->whereNull('parent_id')->where('is_active',1)->orderBy('urutan')->get();
                                        @endphp
                                        @foreach($l1s as $l1)
                                            @php
                                                $l2s = \App\Models\Kategori::where('level',2)->where('parent_id',$l1->kategori_id)->where('is_active',1)->orderBy('urutan')->get();
                                            @endphp
                                            <div>
                                                <div class="text-xs font-bold text-slate-700">{{ $l1->nama_kategori }}</div>
                                                <div class="mt-2 space-y-2">
                                                    @foreach($l2s as $l2)
                                                        @php
                                                            $l3s = \App\Models\Kategori::where('level',3)->where('parent_id',$l2->kategori_id)->where('is_active',1)->orderBy('urutan')->get();
                                                        @endphp
                                                        <div class="group">
                                                            <a class="block rounded-xl px-2 py-1 text-sm hover:bg-[#63a2bb]/10" href="{{ route('category.show',$l2->slug) }}">
                                                                {{ $l2->nama_kategori }}
                                                            </a>
                                                            @if($l3s->isNotEmpty())
                                                                <div class="hidden group-hover:block absolute bg-white border border-[#63a2bb]/25 rounded-xl p-2 mt-2" style="margin-left: 170px; width: 220px;">
                                                                    @foreach($l3s as $l3)
                                                                        <a class="block rounded-lg px-2 py-1 text-sm hover:bg-[#63a2bb]/10" href="{{ route('category.show',$l3->slug) }}">
                                                                            {{ $l3->nama_kategori }}
                                                                        </a>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Search --}}
                    <div class="hidden lg:flex items-center flex-1 justify-center px-6">
                        <form method="get" action="{{ url('/search') }}" class="w-full max-w-xl flex gap-2">
                            <input class="w-full rounded-full border border-[#63a2bb]/25 bg-white px-4 py-2 text-sm outline-none focus:border-[#63a2bb]" name="q" placeholder="{{ __('ui.search_placeholder') }}" />
                            <button class="rounded-full bg-[#63a2bb] px-5 py-2 text-sm font-semibold text-white hover:bg-[#4f90aa]" type="submit">{{ __('ui.search') }}</button>
                        </form>
                    </div>

                    {{-- Icons --}}
                    <div class="flex items-center gap-2">
                        {{-- Mobile hamburger --}}
                        <button type="button" class="md:hidden rounded-full border border-[#63a2bb]/35 bg-[#63a2bb]/10 px-3 py-2 hover:bg-[#63a2bb]/20" @click="drawerOpen=true">
                            <span class="text-sm font-semibold">☰</span>
                        </button>

                        <a href="{{ route('wishlist.index') }}" class="relative rounded-full border border-[#63a2bb]/35 bg-[#63a2bb]/10 px-3 py-2 text-sm hover:bg-[#63a2bb]/20">
                            ♡
                            <span data-wishlist-badge x-text="counts.wishlist" class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center" x-bind:class="{'hidden': counts.wishlist == 0}"></span>
                        </a>
                        <a href="{{ route('cart.index') }}" class="relative rounded-full border border-[#63a2bb]/35 bg-[#63a2bb]/10 px-3 py-2 text-sm hover:bg-[#63a2bb]/20">
                            🛒
                            <span data-cart-badge x-text="counts.cart" class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center" x-bind:class="{'hidden': counts.cart == 0}"></span>
                        </a>

                        <div class="hidden sm:flex items-center rounded-full border border-slate-200 bg-white p-1 text-xs font-semibold">
                            <a href="{{ route('language.switch', 'id') }}" class="rounded-full px-3 py-1.5 {{ app()->getLocale() === 'id' ? 'bg-[#63a2bb] text-white' : 'text-slate-600 hover:bg-slate-100' }}">ID</a>
                            <a href="{{ route('language.switch', 'en') }}" class="rounded-full px-3 py-1.5 {{ app()->getLocale() === 'en' ? 'bg-[#63a2bb] text-white' : 'text-slate-600 hover:bg-slate-100' }}">EN</a>
                        </div>

                        <div class="relative hidden sm:block">
                            @guest
                                <a href="{{ route('login') }}" class="rounded-full border border-[#63a2bb]/35 bg-[#63a2bb]/10 px-4 py-2 text-sm hover:bg-[#63a2bb]/20">{{ __('ui.login') }}</a>
                            @else
                                <a href="{{ route('profile.index') }}" class="rounded-full border border-[#63a2bb]/35 bg-[#63a2bb]/10 px-4 py-2 text-sm hover:bg-[#63a2bb]/20">{{ __('ui.profile') }}</a>
                            @endguest
                        </div>
                    </div>
                </div>
            </div>
        </header>

        @include('buyer.partials.promo-banner')

        {{-- Toast --}}
        <div x-show="toast.show" x-cloak class="fixed bottom-5 left-1/2 -translate-x-1/2 z-[60]">
            <div class="rounded-2xl border border-[#63a2bb]/30 bg-white px-4 py-3 text-sm shadow-lg">
                <div class="font-semibold" x-text="toast.title"></div>
                <div class="opacity-80" x-text="toast.message"></div>
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 text-slate-800">
            @if(session('success'))
                <div class="mb-4 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-2xl border border-rose-400/30 bg-rose-500/10 px-4 py-3">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="border-t border-[#63a2bb]/25 py-6 text-center text-slate-500">
            © {{ date('Y') }} MOVR
        </footer>
    </div>

    <script>
        function movrHeader() {
            return {
                drawerOpen: false,
                categoryHover: false,
                profileOpen: false,
                toast: { show: false, title: '', message: '', timeout: null },
                // counts initialized from server-side values via Blade
                counts: { wishlist: {{ $wishlistCount }}, cart: {{ $cartCount }} },
                refreshCounts(){
                    fetch('/api/wishlist-count').then(r=>r.json()).then(j=>{ this.counts.wishlist = j.count || 0 }).catch(()=>{});
                    fetch('/api/cart-count').then(r=>r.json()).then(j=>{ this.counts.cart = j.count || 0 }).catch(()=>{});
                    // update any DOM fallback badges used by scripts
                    const wb = document.querySelector('[data-wishlist-badge]'); if(wb) wb.textContent = this.counts.wishlist;
                    const cb = document.querySelector('[data-cart-badge]'); if(cb) cb.textContent = this.counts.cart;
                },
                init(){
                    // initial refresh and event listeners
                    this.refreshCounts();
                    window.addEventListener('cart-updated', ()=>{ this.refreshCounts() });
                    window.addEventListener('wishlist-updated', ()=>{ this.refreshCounts() });
                },
                showToast(title, message) {
                    this.toast.title = title;
                    this.toast.message = message;
                    this.toast.show = true;
                    clearTimeout(this.toast.timeout);
                    this.toast.timeout = setTimeout(() => { this.toast.show = false }, 2500);
                }
            }
        }
    </script>
    @stack('scripts')
</body>
</html>

