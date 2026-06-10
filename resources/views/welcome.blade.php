<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Imam-StarterKit - Laravel 11, Livewire Volt, and Alpine.js premium starter kit for rapid application development.">
    <title>{{ $identitas?->nama_aplikasi ?? 'Imam-StarterKit' }} – Starter Kit Premium</title>

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

        /* ── HERO SLIDER ──────────────────────────────────────── */
        .hero-slider-wrapper {
            position: relative;
            overflow: hidden;
            min-height: 480px;
        }

        @media(min-width:768px) {
            .hero-slider-wrapper {
                min-height: 550px;
            }
        }

        .hero-slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity .8s ease;
            background-size: cover;
            background-position: center;
        }

        .hero-slide.active {
            opacity: 1;
            z-index: 1;
        }

        .hero-slide .overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, .65) 0%, rgba(0, 0, 0, .35) 60%, rgba(0, 0, 0, .15) 100%);
        }

        .hero-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px 0;
        }

        .hero-content h1 {
            font-size: clamp(1.8rem, 4.5vw, 3rem);
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
            margin-bottom: 1rem;
            text-shadow: 0 2px 15px rgba(0, 0, 0, .3);
        }

        .hero-content p {
            font-size: clamp(.95rem, 2vw, 1.15rem);
            color: rgba(255, 255, 255, .9);
            max-width: 580px;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .btn-hero-primary {
            background: #fff;
            color: var(--brand);
            padding: .75rem 1.75rem;
            border-radius: 10px;
            font-weight: 700;
            text-decoration: none;
            font-size: .95rem;
            transition: all .25s ease;
            display: inline-block;
            border: none;
        }

        .btn-hero-primary:hover {
            background: var(--brand);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, .4);
        }

        .btn-hero-outline {
            background: transparent;
            color: #fff;
            border: 2px solid rgba(255, 255, 255, .75);
            padding: .7rem 1.6rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            font-size: .92rem;
            transition: all .25s ease;
            display: inline-block;
        }

        .btn-hero-outline:hover {
            background: rgba(255, 255, 255, .15);
            color: #fff;
            border-color: #fff;
        }

        .slider-dots {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
            z-index: 10;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .4);
            cursor: pointer;
            transition: .3s ease;
            border: none;
            padding: 0;
        }

        .dot.active {
            background: #fff;
            width: 28px;
            border-radius: 5px;
        }

        .slider-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            background: rgba(255, 255, 255, .15);
            border: none;
            color: #fff;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            transition: .25s ease;
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .slider-arrow:hover {
            background: rgba(255, 255, 255, .35);
        }

        .slider-arrow.prev { left: 20px; }
        .slider-arrow.next { right: 20px; }

        /* ── STATS BAR ──────────────────────────────────────── */
        .stats-bar {
            padding: 0;
            margin-top: -3.5rem;
            position: relative;
            z-index: 5;
        }

        .stat-card {
            background: #fff;
            border-radius: 20px;
            padding: 1.75rem 2rem;
            box-shadow: 0 12px 35px rgba(0, 0, 0, .07);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            border-left: 5px solid var(--c, var(--brand));
            height: 100%;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.12);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: var(--bg, rgba(37, 99, 235, .1));
            color: var(--c, var(--brand));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            flex-shrink: 0;
        }

        .stat-label {
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            margin-bottom: .25rem;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text);
            line-height: 1.1;
        }

        /* ── SECTION GENERICS ──────────────────────────────── */
        .section {
            padding: 5rem 0;
        }

        .section-alt {
            background: var(--bg-light);
        }

        .section-title {
            font-size: clamp(1.5rem, 3.5vw, 2.2rem);
            font-weight: 800;
            color: var(--text);
            margin-bottom: .5rem;
        }

        .section-sub {
            color: var(--muted);
            font-size: 1rem;
            margin-bottom: 3rem;
            max-width: 600px;
        }

        .section-label {
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--brand);
            margin-bottom: .6rem;
        }

        /* ── BERITA CARD ──────────────────────────────────── */
        .berita-card {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(0, 0, 0, .04);
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            height: 100%;
            background: #fff;
        }

        .berita-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, .1);
            border-color: var(--brand);
        }

        .berita-img {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }

        .berita-no-img {
            width: 100%;
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
        }

        .berita-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .kat-badge {
            font-size: .72rem;
            font-weight: 700;
            padding: .25rem .75rem;
            border-radius: 6px;
            margin-bottom: .75rem;
            align-self: flex-start;
        }

        .kat-Berita { background: #eff6ff; color: #2563eb; }
        .kat-Pengumuman { background: #fffbeb; color: #d97706; }
        .kat-Kegiatan { background: #f0fdf4; color: #16a34a; }

        .berita-body h5 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text);
            line-height: 1.4;
            margin-bottom: .5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .berita-body p {
            font-size: .88rem;
            color: var(--muted);
            line-height: 1.5;
            margin-bottom: 1.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-grow: 1;
        }

        .berita-footer {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: .78rem;
            color: var(--muted);
        }

        /* ── FOOTER ──────────────────────────────────────── */
        .smart-footer {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #cbd5e1;
            padding: 4rem 0 1.5rem;
        }

        .footer-brand { font-size: 1.35rem; font-weight: 800; color: #fff; margin-bottom: 1rem; }
        .footer-desc { font-size: .9rem; color: #94a3b8; max-width: 320px; line-height: 1.6; }
        .footer-title { font-size: .88rem; font-weight: 700; text-transform: uppercase; letter-spacing: .75px; color: #e2e8f0; margin-bottom: 1.25rem; }
        .footer-links a { display: block; color: #94a3b8; text-decoration: none; font-size: .9rem; margin-bottom: .6rem; transition: .2s ease; }
        .footer-links a:hover { color: #fff; padding-left: 3px; }
        .contact-item { display: flex; gap: .75rem; align-items: flex-start; font-size: .9rem; margin-bottom: .88rem; }
        .contact-item i { color: var(--accent); margin-top: .2rem; flex-shrink: 0; font-size: 1.15rem; }

        @media (max-width: 991px) {
            .nav-links { display: none !important; }
            .hamburger { display: flex; }
            .stats-bar { margin-top: -2.5rem; }
        }
    </style>
</head>
<body>

    @php
        use App\Models\Identitas;
        use App\Models\User;
        use App\Models\FormGenerator;
        use App\Models\Berita;
        use App\Models\KontenSlider;

        $identitas = Identitas::first();
        $sliders = KontenSlider::where('is_active', true)->orderBy('urutan')->get();
        $totalUsers = User::count();
        $totalForms = FormGenerator::count();
        $beritaList = Berita::where('is_published', true)->where('is_featured', true)->orderByDesc('tanggal_terbit')->take(3)->get();
        
        $namaApp = $identitas?->nama_aplikasi ?? 'Imam-StarterKit';
        $singkatanApp = $identitas?->singkatan_aplikasi ?? 'Imam-StarterKit';
        $slogan = $identitas?->slogan ?? 'Starter kit Laravel premium dengan performa tinggi & desain kelas dunia.';
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
                <li><a href="#slider">Home</a></li>
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
                <a href="#slider">🏠 Home</a>
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

    <!-- HERO SLIDER SECTION -->
    <section class="hero-slider-wrapper" id="slider">
        @forelse($sliders as $i => $sl)
            <div class="hero-slide {{ $i===0 ? 'active' : '' }}" data-slide="{{ $i }}"
                 style="background-image:url('{{ asset('storage/' . $sl->foto) }}');">
                <div class="overlay" style="background:linear-gradient(135deg, {{ $sl->warna_latar }}cc 0%, {{ $sl->warna_latar }}66 60%, rgba(0,0,0,.2) 100%);"></div>
                <div class="container hero-content">
                    <div style="max-width:620px;">
                        <h1>{{ $sl->judul }}</h1>
                        @if($sl->subjudul)
                            <p>{{ $sl->subjudul }}</p>
                        @endif
                        <div class="d-flex flex-wrap gap-3">
                            @if($sl->label_tombol && $sl->url_tombol)
                                <a href="{{ $sl->url_tombol }}" class="btn-hero-primary">{{ $sl->label_tombol }}</a>
                            @else
                                <a href="{{ route('login') }}" class="btn-hero-primary"><i class='bx bx-log-in me-1'></i>Masuk Sekarang</a>
                            @endif
                            <a href="/berita" class="btn-hero-outline"><i class='bx bx-news me-1'></i>Lihat Berita</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <!-- Fallback slide if no slider data is seeded -->
            <div class="hero-slide active" style="background: linear-gradient(135deg, #1e40af 0%, #7c3aed 100%);">
                <div class="container hero-content">
                    <div style="max-width:620px;">
                        <h1>{{ $slogan }}</h1>
                        <p>Sebuah starter kit modern yang dirancang untuk mempercepat pengembangan aplikasi web Anda menggunakan Laravel 11, Livewire Volt, dan panel admin Sneat.</p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="{{ route('login') }}" class="btn-hero-primary">Masuk Sekarang</a>
                            <a href="/berita" class="btn-hero-outline">Lihat Berita</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforelse

        @if($sliders->count() > 1)
            <button class="slider-arrow prev" id="sliderPrev"><i class='bx bx-chevron-left'></i></button>
            <button class="slider-arrow next" id="sliderNext"><i class='bx bx-chevron-right'></i></button>
            <div class="slider-dots" id="sliderDots">
                @foreach($sliders as $i => $sl)
                    <button class="dot {{ $i===0 ? 'active' : '' }}" data-target="{{ $i }}"></button>
                @endforeach
            </div>
        @endif
    </section>

    <!-- STATS BAR SECTION -->
    <section class="stats-bar">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-md-4 col-sm-6">
                    <div class="stat-card" style="--c:#2563eb; --bg:rgba(37,99,235,.1);">
                        <div class="stat-icon"><i class='bx bx-user-voice'></i></div>
                        <div>
                            <div class="stat-label">Total Pengembang</div>
                            <div class="stat-value" id="counterUsers" data-target="{{ $totalUsers }}">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="stat-card" style="--c:#7c3aed; --bg:rgba(124,58,237,.1);">
                        <div class="stat-icon"><i class='bx bx-code-block'></i></div>
                        <div>
                            <div class="stat-label">Form Generator</div>
                            <div class="stat-value" id="counterForms" data-target="{{ $totalForms }}">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="stat-card" style="--c:#10b981; --bg:rgba(16,185,129,.1);">
                        <div class="stat-icon"><i class='bx bx-check-shield'></i></div>
                        <div>
                            <div class="stat-label">Status Lisensi</div>
                            <div class="stat-value" style="font-size:1.5rem; font-weight:800; color:#10b981;">Ready-to-Use</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- DYNAMIC FEATURED NEWS SECTION -->
    <section class="section section-alt" id="berita">
        <div class="container">
            <div class="text-center mb-5">
                <div class="section-label">Informasi Proyek</div>
                <h2 class="section-title">Berita & Kegiatan</h2>
                <p class="section-sub mx-auto">Ikuti rilisan pembaruan fitur, dokumentasi instalasi, dan panduan development Imam-StarterKit.</p>
            </div>

            <div class="row g-4">
                @forelse($beritaList as $b)
                    <div class="col-lg-4 col-md-6">
                        <a href="{{ route('berita.detail', $b->slug) }}" class="berita-card">
                            @if($b->foto)
                                <img src="{{ asset('storage/' . $b->foto) }}" alt="{{ $b->judul }}" class="berita-img">
                            @else
                                <div class="berita-no-img">
                                    <i class='bx bx-news text-primary' style="font-size:3.5rem; opacity:.25;"></i>
                                </div>
                            @endif
                            <div class="berita-body">
                                <span class="kat-badge kat-{{ $b->kategori }}">{{ $b->kategori }}</span>
                                <h5>{{ $b->judul }}</h5>
                                <p>{{ $b->ringkasan }}</p>
                                <div class="berita-footer">
                                    <span><i class='bx bx-calendar me-1'></i>{{ $b->tanggal_terbit?->format('d M Y') }}</span>
                                    <span><i class='bx bx-user me-1'></i>{{ $b->penulis }}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-12 text-center py-5 text-muted">
                        <i class='bx bx-news fs-1 mb-2 d-block opacity-25'></i> Belum ada berita/kegiatan Imam-StarterKit saat ini.
                    </div>
                @endforelse
            </div>

            @if($beritaList->count() > 0)
                <div class="text-center mt-5">
                    <a href="/berita" class="btn btn-outline-primary rounded-pill px-4">Lihat Semua Berita →</a>
                </div>
            @endif
        </div>
    </section>

    <!-- FOOTER SECTION -->
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
                        <a href="{{ route('login') }}">Masuk Dashboard</a>
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

        // Hero Slider Logic
        (function() {
            const slides = document.querySelectorAll('.hero-slide');
            const dots = document.querySelectorAll('.dot');
            if (!slides.length) return;

            let current = 0;
            let timer;

            function goTo(idx) {
                slides[current].classList.remove('active');
                if (dots[current]) dots[current].classList.remove('active');
                current = (idx + slides.length) % slides.length;
                slides[current].classList.add('active');
                if (dots[current]) dots[current].classList.add('active');
            }

            function autoPlay() {
                timer = setInterval(() => goTo(current + 1), 6000);
            }

            document.getElementById('sliderPrev')?.addEventListener('click', () => {
                clearInterval(timer);
                goTo(current - 1);
                autoPlay();
            });
            document.getElementById('sliderNext')?.addEventListener('click', () => {
                clearInterval(timer);
                goTo(current + 1);
                autoPlay();
            });
            dots.forEach((dot, i) => dot.addEventListener('click', () => {
                clearInterval(timer);
                goTo(i);
                autoPlay();
            }));

            // Touch Swipe support
            let startX = 0;
            const wrapper = document.querySelector('.hero-slider-wrapper');
            wrapper?.addEventListener('touchstart', (e) => startX = e.touches[0].clientX, { passive: true });
            wrapper?.addEventListener('touchend', (e) => {
                const diff = startX - e.changedTouches[0].clientX;
                if (Math.abs(diff) > 50) {
                    clearInterval(timer);
                    goTo(diff > 0 ? current + 1 : current - 1);
                    autoPlay();
                }
            }, { passive: true });

            autoPlay();
        })();

        // Counter Animation
        function animateCounter(el) {
            const target = parseInt(el.dataset.target || 0);
            if (target === 0) return;
            const duration = 1200;
            const step = target / (duration / 16);
            let current = 0;
            const update = () => {
                current = Math.min(current + step, target);
                el.textContent = Math.floor(current).toLocaleString('id-ID');
                if (current < target) requestAnimationFrame(update);
            };
            requestAnimationFrame(update);
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    animateCounter(e.target);
                    observer.unobserve(e.target);
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('[data-target]').forEach(el => observer.observe(el));
    </script>
</body>
</html>
