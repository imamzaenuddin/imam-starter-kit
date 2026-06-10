<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Imam-StarterKit') – Developer Starter Kit</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @include('partials.head')

    <style>
        :root {
            --brand: #2563eb;
            --brand-dark: #1e40af;
            --accent: #f59e0b;
            --green: #10b981;
            --purple: #7c3aed;
            --text: #1e293b;
            --muted: #64748b;
            --bg-light: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text);
            background: #fff;
            margin: 0;
            overflow-x: hidden;
        }

        /* ── NAVBAR ──────────────────────────────────────── */
        .smart-nav {
            position: sticky;
            top: 0;
            z-index: 1030;
            background: rgba(255, 255, 255, .95);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(0, 0, 0, .06);
            padding: .75rem 0;
            transition: all .3s ease;
        }

        .smart-nav.scrolled {
            box-shadow: 0 4px 20px rgba(0, 0, 0, .06);
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: .6rem;
            text-decoration: none;
        }

        .nav-logo img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: contain;
        }

        .nav-logo .brand-name {
            font-weight: 800;
            font-size: 1.15rem;
            color: var(--brand);
            line-height: 1.1;
            white-space: nowrap;
        }

        .nav-logo .brand-sub {
            font-size: .68rem;
            color: var(--muted);
            font-weight: 600;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
            margin: 0;
            padding: 0;
            align-items: center;
        }

        .nav-links a {
            color: var(--muted);
            text-decoration: none;
            font-size: .9rem;
            font-weight: 600;
            transition: color .2s ease;
        }

        .nav-links a:hover {
            color: var(--brand);
        }

        .btn-masuk {
            background: var(--brand);
            color: #fff;
            border: none;
            padding: .5rem 1.25rem;
            border-radius: 8px;
            font-size: .88rem;
            font-weight: 600;
            text-decoration: none;
            transition: all .25s ease;
            display: inline-block;
        }

        .btn-masuk:hover {
            background: var(--brand-dark);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, .25);
        }

        .btn-login {
            background: #f1f5f9;
            color: var(--text);
            border: 1px solid #cbd5e1;
            padding: .5rem 1.25rem;
            border-radius: 8px;
            font-size: .88rem;
            font-weight: 600;
            text-decoration: none;
            transition: all .25s ease;
            display: inline-block;
        }

        .btn-login:hover {
            background: #e2e8f0;
            color: var(--text);
            transform: translateY(-1px);
        }

        .hamburger {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: .25rem;
            flex-direction: column;
            justify-content: center;
        }

        .hamburger .bar {
            display: block;
            width: 22px;
            height: 2px;
            background: var(--text);
            border-radius: 2px;
            margin: 4px 0;
            transition: .3s ease;
        }

        .hamburger.active .bar:nth-child(1) {
            transform: translateY(6px) rotate(45deg);
        }

        .hamburger.active .bar:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active .bar:nth-child(3) {
            transform: translateY(-6px) rotate(-45deg);
        }

        .mobile-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.98);
            border-bottom: 1px solid rgba(0, 0, 0, .08);
            padding: 1.5rem;
            flex-direction: column;
            gap: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
            border-top: 1px solid #f1f5f9;
        }

        .mobile-menu.open {
            display: flex;
        }

        .mobile-menu a {
            color: var(--muted);
            text-decoration: none;
            font-weight: 600;
            font-size: .95rem;
            padding: .4rem 0;
            border-bottom: 1px solid #f1f5f9;
            transition: color 0.2s;
        }

        .mobile-menu a:hover {
            color: var(--brand);
        }

        /* ── FOOTER ──────────────────────────────────────── */
        .smart-footer {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #cbd5e1;
            padding: 3.5rem 0 1.5rem;
            margin-top: 5rem;
        }

        .footer-brand {
            font-size: 1.25rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: .75rem;
        }

        .footer-desc {
            font-size: .88rem;
            color: #94a3b8;
            max-width: 300px;
            line-height: 1.6;
        }

        .footer-title {
            font-size: .85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .75px;
            color: #e2e8f0;
            margin-bottom: 1rem;
        }

        .footer-links a {
            display: block;
            color: #94a3b8;
            text-decoration: none;
            font-size: .88rem;
            margin-bottom: .5rem;
            transition: .2s ease;
        }

        .footer-links a:hover {
            color: #fff;
            padding-left: 3px;
        }

        .contact-item {
            display: flex;
            gap: .75rem;
            align-items: flex-start;
            font-size: .88rem;
            margin-bottom: .75rem;
        }

        .contact-item i {
            color: var(--accent);
            margin-top: .2rem;
            flex-shrink: 0;
            font-size: 1.1rem;
        }

        .footer-divider {
            border-color: rgba(255, 255, 255, .08);
        }

        .footer-bottom {
            font-size: .8rem;
            color: #64748b;
            padding-top: 1rem;
        }

        @media (max-width: 991px) {
            .nav-links {
                display: none !important;
            }

            .hamburger {
                display: flex;
            }
        }
    </style>
</head>
<body>

    @php
        use App\Models\Identitas;
        $identitas = Identitas::first();
        $namaApp = $identitas?->nama_aplikasi ?? 'Imam-StarterKit';
        $singkatanApp = $identitas?->singkatan_aplikasi ?? 'Imam-StarterKit';
    @endphp

    <!-- NAVBAR -->
    <nav class="smart-nav" id="mainNav">
        <div class="container d-flex align-items-center justify-content-between gap-3" style="position:relative;">
            <a href="/" class="nav-logo">
                @if($identitas?->logo_path)
                    <img src="{{ asset('storage/' . $identitas->logo_path) }}" alt="Logo {{ $namaApp }}">
                @else
                    <div style="width:40px;height:40px;border-radius:8px;background:var(--brand);display:flex;align-items:center;justify-content:center;">
                        <i class='bx bx-building-house text-white' style="font-size:1.3rem;"></i>
                    </div>
                @endif
                <div>
                    <div class="brand-name">{{ $singkatanApp }}</div>
                    <div class="brand-sub">{{ $namaApp }}</div>
                </div>
            </a>

            <ul class="nav-links">
                <li><a href="/#slider">Home</a></li>
                <li><a href="/berita">Berita</a></li>
                <li><a href="#kontak">Kontak</a></li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-masuk d-none d-lg-inline-block">Dashboard</a>
                @else
                    @if(Route::has('login'))
                        <a href="{{ route('login') }}" class="btn-login d-none d-lg-inline-block">Masuk</a>
                    @endif
                @endauth
                <button class="hamburger" id="hamburgerBtn" aria-label="Menu">
                    <span class="bar"></span><span class="bar"></span><span class="bar"></span>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div class="mobile-menu" id="mobileMenu">
                <a href="/#slider">🏠 Home</a>
                <a href="/berita">📰 Berita</a>
                <a href="#kontak">📞 Kontak</a>
                @auth
                    <a href="{{ url('/dashboard') }}" style="color:var(--brand);">🔐 Dashboard</a>
                @else
                    @if(Route::has('login'))
                        <a href="{{ route('login') }}" style="color:#2563eb;">🔑 Masuk</a>
                    @endif
                @endauth
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main>
        {{ $slot }}
    </main>

    <!-- FOOTER -->
    <footer class="smart-footer" id="kontak">
        <div class="container">
            <div class="row g-4 pb-3">
                <div class="col-lg-4 col-md-6">
                    <div class="footer-brand">{{ $namaApp }}</div>
                    <div class="footer-desc">{{ $identitas?->deskripsi ?? 'Starter kit Laravel 11 dengan performa tinggi, UI premium berbasis Livewire Volt, dan Form Generator wizard.' }}</div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="footer-title">Tautan Berguna</div>
                    <div class="footer-links">
                        <a href="/berita">Berita & Artikel</a>
                        <a href="{{ route('login') }}">Dashboard</a>
                        <a href="https://laravel.com" target="_blank">Laravel Docs</a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="footer-title">Kontak Info</div>
                    @if($identitas?->telepon)
                        <div class="contact-item"><i class='bx bx-phone'></i><span>{{ $identitas->telepon }}</span></div>
                    @endif
                    @if($identitas?->email)
                        <div class="contact-item"><i class='bx bx-envelope'></i><span>{{ $identitas->email }}</span></div>
                    @endif
                    @if($identitas?->wa_center)
                        <div class="contact-item"><i class='bx bxl-whatsapp'></i><span>{{ $identitas->wa_center }}</span></div>
                    @endif
                    @if($identitas?->alamat)
                        <div class="contact-item"><i class='bx bx-map'></i><span>{{ $identitas->alamat }}</span></div>
                    @endif
                </div>
            </div>
            <hr class="footer-divider">
            <div class="footer-bottom text-center">
                © {{ date('Y') }} {{ $namaApp }}. Semua Hak Dilindungi. Built with ❤️ for developers.
            </div>
        </div>
    </footer>

    @include('partials.scripts')

    <script>
        // Navbar scroll shadow
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('mainNav');
            if (nav) {
                nav.classList.toggle('smart-nav scrolled', window.scrollY > 20);
            }
        });

        // Mobile menu hamburger toggle
        const hamburger = document.getElementById('hamburgerBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        if (hamburger) {
            hamburger.addEventListener('click', () => {
                mobileMenu.classList.toggle('open');
                hamburger.classList.toggle('active');
            });
            document.addEventListener('click', (e) => {
                if (!hamburger.contains(e.target) && !mobileMenu.contains(e.target)) {
                    mobileMenu.classList.remove('open');
                    hamburger.classList.remove('active');
                }
            });
        }
    </script>
</body>
</html>
