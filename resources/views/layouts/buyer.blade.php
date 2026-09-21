<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MOVR — Move With Style')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }

        :root {
            --primary: #63A2BB;
            --primary-dark: #4A8BA3;
            --primary-light: #8EC4D6;
            --surface: #FFFFFF;
            --page: #F8FAFB;
            --card: #F1F5F8;
            --text-secondary: #94A3B8;
            --text-primary: #334155;
            --text-strong: #0F172A;
            --danger: #EF4444;
        }

        html, body {
            font-family: Inter, system-ui, sans-serif;
            background: var(--page);
            color: var(--text-primary);
        }

        body {
            min-height: 100vh;
        }

        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: var(--card); }
        ::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 9999px; }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 9999px;
            background: #63A2BB;
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #FFFFFF;
            transition: all 200ms ease-in-out;
        }

        .btn-primary:hover {
            background: #4A8BA3;
            transform: scale(1.02);
            box-shadow: 0 12px 28px rgba(99, 162, 187, 0.22);
        }

        .btn-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 9999px;
            border: 1px solid rgba(99, 162, 187, 0.25);
            background: #FFFFFF;
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #63A2BB;
            transition: all 200ms ease-in-out;
        }

        .btn-outline:hover {
            background: #63A2BB;
            color: #FFFFFF;
            transform: scale(1.02);
            box-shadow: 0 12px 28px rgba(99, 162, 187, 0.18);
        }

        .card-surface {
            border-radius: 1.5rem;
            border: 1px solid #E2E8F0;
            background: #FFFFFF;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            transition: all 200ms ease-in-out;
        }

        .card-hover {
            transition: all 200ms ease-in-out;
        }

        .card-hover:hover {
            transform: scale(1.02);
            box-shadow: 0 18px 40px rgba(99, 162, 187, 0.15);
        }

        .section-shell {
            width: 100%;
            max-width: 80rem;
            margin-left: auto;
            margin-right: auto;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        @media (min-width: 640px) {
            .section-shell {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }

        @media (min-width: 1024px) {
            .section-shell {
                padding-left: 2rem;
                padding-right: 2rem;
            }
        }
    </style>
    @stack('head')
</head>
<body>
    <div class="min-h-screen bg-[#F8FAFB] text-slate-800">
        @include('buyer.partials.header')
        @include('buyer.partials.promo-banner')

        <main class="min-h-screen">
            @yield('content')
        </main>

        @include('buyer.partials.footer')

        <div id="toast-container" class="fixed bottom-6 right-6 z-[9999] flex flex-col gap-2 max-w-sm pointer-events-none"></div>
    </div>

    <script>
        function showToast(message, type = 'success') {
            const colors = {
                success: 'bg-[#63A2BB] text-white',
                error: 'bg-red-500 text-white',
                warning: 'bg-amber-500 text-white',
                info: 'bg-blue-500 text-white'
            };

            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `pointer-events-auto px-5 py-3 rounded-2xl shadow-xl text-sm font-medium flex items-center gap-2 transform translate-y-4 opacity-0 transition-all duration-300 ${colors[type] || colors.success}`;
            toast.innerHTML = message;
            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            });

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>

    @yield('scripts')
</body>
</html>
