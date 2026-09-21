{{--
  // ── FILE: resources/views/buyer/partials/promo-banner.blade.php ──
  Banner promo/diskon berwarna merah (Clean English Copy)
--}}
<div x-data="{ showBanner: true }" x-show="showBanner" x-transition.opacity class="w-full relative z-30 shadow-sm">
    {{-- Main Red Banner Section (Slim Fixed Height: h-[64px] ~ h-[74px]) --}}
    <div class="relative w-full bg-[#E31837] h-[64px] sm:h-[74px] flex items-center overflow-hidden">
        
        {{-- Left Sports Photo Container (Uses exact user-provided athlete image) --}}
        <div class="w-[30%] sm:w-[26%] md:w-[24%] lg:w-[22%] shrink-0 h-full relative overflow-hidden bg-slate-900 z-0">
            <img src="{{ asset('images/banner-sports-shoe.jpg') }}" 
                 alt="MOVR Athlete Running Shoes" 
                 class="w-full h-full object-cover object-[center_65%] filter brightness-95 contrast-105">
            {{-- Subtle gradient overlay --}}
            <div class="absolute inset-0 bg-gradient-to-r from-black/15 via-transparent to-transparent"></div>
        </div>

        {{-- Red Slanted Banner Content Box --}}
        <div class="relative flex-1 h-full bg-[#E31837] text-white flex items-center justify-between px-3 sm:px-6 md:px-8 -ml-6 sm:-ml-8 md:-ml-10 z-10 
                    [clip-path:polygon(35px_0,100%_0,100%_100%,0_100%)] sm:[clip-path:polygon(45px_0,100%_0,100%_100%,0_100%)]">
            
            {{-- Clean English Promo Text Content --}}
            <div class="py-1 pl-6 sm:pl-8 md:pl-6 pr-2 flex-1 max-w-2xl min-w-0">
                <h2 class="text-xs sm:text-base md:text-lg font-extrabold text-white tracking-wider leading-tight truncate uppercase font-sans">
                    FLASH SALE — UP TO 50% OFF
                </h2>
                <p class="text-[11px] sm:text-xs text-white/90 font-sans font-medium mt-0.5 leading-none truncate tracking-wide">
                    Enjoy exclusive savings and free shipping on selected footwear.
                </p>
            </div>

            {{-- Right CTA & Dismiss Button --}}
            <div class="flex items-center gap-2 sm:gap-3 shrink-0 py-1">
                <a href="{{ route('product.index') }}" 
                   class="inline-flex items-center gap-1.5 bg-white text-[#E31837] hover:bg-slate-100 font-extrabold text-[11px] sm:text-xs px-3.5 sm:px-5 py-1.5 sm:py-2 rounded-full shadow-sm transition-all transform hover:scale-105 uppercase tracking-wider group whitespace-nowrap">
                    <span>Shop Sale</span>
                    <span class="text-xs transition-transform group-hover:translate-x-0.5">→</span>
                </a>

                {{-- Dismiss Button --}}
                <button @click="showBanner = false" 
                        class="text-white/80 hover:text-white p-1 rounded-full hover:bg-white/10 transition-colors ml-1"
                        title="Dismiss banner">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
