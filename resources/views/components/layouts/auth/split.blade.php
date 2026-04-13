<style>
    /* =============================================
      ISK - Imam Starter-Kit Auth Layout
      ============================================= */
  * { box-sizing: border-box; }
  body { margin: 0; padding: 0; }

  .isk-auth-wrapper {
    min-height: 100vh;
    display: flex;
    font-family: 'Public Sans', sans-serif;
    --sio-auth-main: var(--sio-main-color, #696cff);
    --sio-auth-secondary: var(--sio-secondary-color, #8592a3);
  }

  /* ---- Left Branding Panel ---- */
  .isk-left-panel {
    background: linear-gradient(145deg, #0f172a 0%, color-mix(in srgb, var(--sio-auth-main) 32%, #0f172a) 45%, color-mix(in srgb, var(--sio-auth-secondary) 35%, #111827) 100%);
    position: relative;
    overflow: hidden;
    padding: 2.5rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  /* Decorative floating circles */
  .isk-decor {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
  }
  .isk-decor-1 {
    width: 520px; height: 520px;
    background: rgba(139, 92, 246, 0.12);
    top: -160px; right: -140px;
    animation: isk-float 8s ease-in-out infinite;
  }
  .isk-decor-2 {
    width: 380px; height: 380px;
    background: rgba(99, 102, 241, 0.10);
    bottom: -100px; left: -100px;
    animation: isk-float 10s ease-in-out infinite reverse;
  }
  .isk-decor-3 {
    width: 220px; height: 220px;
    background: rgba(167, 139, 250, 0.08);
    top: 45%; right: 5%;
    animation: isk-float 7s ease-in-out infinite 2s;
  }
  .isk-decor-4 {
    width: 100px; height: 100px;
    background: rgba(255, 255, 255, 0.06);
    top: 22%; left: 8%;
    animation: isk-float 9s ease-in-out infinite 1s;
  }
  .isk-decor-5 {
    width: 60px; height: 60px;
    background: rgba(196, 181, 253, 0.15);
    bottom: 28%; right: 15%;
    border-radius: 12px;
    transform: rotate(30deg);
    animation: isk-spin 15s linear infinite;
  }

  @keyframes isk-float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-18px); }
  }
  @keyframes isk-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }

  /* Top badge */
  .isk-brand-badge {
    display: inline-flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 50px;
    padding: 0.45rem 1.1rem;
    width: fit-content;
  }

  /* Center hero icon */
  .isk-hero-icon {
    width: 110px;
    height: 110px;
    background: rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(12px);
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.75rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255,255,255,0.2);
  }

  /* Feature pills */
  .isk-feature-pill {
    display: inline-flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.10);
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 50px;
    padding: 0.4rem 0.9rem;
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.78rem;
    font-weight: 500;
    transition: background 0.2s;
  }
  .isk-feature-pill:hover {
    background: rgba(255, 255, 255, 0.18);
  }

  /* Stats row */
  .isk-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    margin-top: 2.5rem;
  }
  .isk-stat-card {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 14px;
    padding: 0.9rem 0.5rem;
    text-align: center;
  }
  .isk-stat-num {
    font-size: 1.4rem;
    font-weight: 700;
    color: #fff;
    line-height: 1.1;
  }
  .isk-stat-label {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.6);
    margin-top: 0.2rem;
  }

  /* ---- Right Form Panel ---- */
  .isk-right-panel {
    background: linear-gradient(160deg, #eff6ff 0%, #f5f3ff 50%, #fdf4ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1.5rem;
    min-height: 100vh;
  }

  .isk-form-card {
    background: #ffffff;
    border-radius: 24px;
    box-shadow:
      0 0 0 1px rgba(99, 102, 241, 0.08),
      0 4px 6px -1px rgba(99, 102, 241, 0.06),
      0 20px 50px -12px rgba(99, 102, 241, 0.18);
    padding: 2.25rem 2rem;
    width: 100%;
    max-width: 420px;
  }

  /* Small logo for desktop top-left of form */
  .isk-form-logo {
    width: 44px; height: 44px;
    background: linear-gradient(135deg, var(--sio-auth-main), var(--sio-auth-secondary));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
  }

  /* Form inputs */
  .isk-form-card .form-control,
  .isk-form-card .input-group-text {
    border-color: #e2e8f0;
    background: #f8fafc;
    transition: all 0.2s;
  }
  .isk-form-card .form-control:focus {
    border-color: var(--sio-auth-main);
    background: #fff;
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--sio-auth-main) 24%, transparent);
  }
  .isk-form-card .input-group:focus-within .input-group-text {
    border-color: var(--sio-auth-main);
    background: #fff;
  }
  .isk-form-card .input-group-text {
    border-right: 0;
  }
  .isk-form-card .input-group .form-control {
    border-left: 0;
  }
  .isk-form-card .input-group .form-control.toggle-end {
    border-left: 0;
    border-right: 0;
  }
  .isk-form-card .input-group .toggle-btn {
    border: 1px solid #e2e8f0;
    border-left: 0;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.2s;
  }
  .isk-form-card .input-group:focus-within .toggle-btn {
    border-color: var(--sio-auth-main);
    background: #fff;
  }

  /* Gradient submit button */
  .isk-btn-submit {
    background: linear-gradient(135deg, var(--sio-auth-main) 0%, var(--sio-auth-secondary) 100%);
    border: none;
    border-radius: 12px;
    color: #fff;
    font-weight: 600;
    font-size: 0.95rem;
    letter-spacing: 0.3px;
    padding: 0.7rem 1.5rem;
    width: 100%;
    box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
    transition: all 0.25s;
    position: relative;
    overflow: hidden;
  }
  .isk-btn-submit::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.15), transparent);
    opacity: 0;
    transition: opacity 0.2s;
  }
  .isk-btn-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
  }
  .isk-btn-submit:hover::before { opacity: 1; }
  .isk-btn-submit:active { transform: translateY(0); }

  /* Checkbox accent */
  .isk-form-card .form-check-input:checked {
    background-color: var(--sio-auth-main);
    border-color: var(--sio-auth-main);
  }
  .isk-form-card .form-check-input:focus {
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--sio-auth-main) 25%, transparent);
    border-color: var(--sio-auth-main);
  }

  .isk-typing-caret::after {
    content: '|';
    margin-left: 2px;
    animation: isk-caret-blink .9s step-end infinite;
  }

  @keyframes isk-caret-blink {
    0%, 49% { opacity: 1; }
    50%, 100% { opacity: 0; }
  }

  @media (max-width: 991.98px) {
    .isk-form-card {
      max-width: 100%;
      border-radius: 20px;
      padding: 1.75rem 1.25rem;
    }
  }
</style>

@php
  use Illuminate\Support\Str;

  $identitas = app(\App\Services\IdentitasService::class)->aktif();
  $namaAplikasi = $identitas?->nama_aplikasi ?? 'Imam Starter-Kit';
  $singkatanAplikasi = $identitas?->singkatan_aplikasi ?? 'ISK';
  $versiAplikasi = $identitas?->versi ?? '1.0.0';
  $sloganAplikasi = $identitas?->slogan ?? 'Platform terintegrasi untuk pengelolaan organisasi.';
  $deskripsiAplikasi = $identitas?->deskripsi ?? 'Platform terintegrasi untuk mengelola data organisasi, sumber daya manusia, dan seluruh aktivitas organisasi secara efisien dan real-time.';
  $logoDefault = asset('assets/img/identitas/gedung-default.svg');
  $logoPath = trim((string) ($identitas?->logo_path ?? ''));
  $logoAplikasi = $logoDefault;
  if ($logoPath !== '') {
    if (Str::startsWith($logoPath, ['http://', 'https://'])) {
      $logoAplikasi = $logoPath;
    } elseif (Str::startsWith($logoPath, ['storage/', '/storage/'])) {
      $logoAplikasi = asset(ltrim($logoPath, '/'));
    } else {
      $logoAplikasi = asset('storage/' . ltrim($logoPath, '/'));
    }
  }
  $logoVersi = (string) ($identitas?->updated_at?->timestamp ?? '1');
  $logoAplikasiFinal = Str::contains($logoAplikasi, '?')
      ? $logoAplikasi . '&v=' . $logoVersi
      : $logoAplikasi . '?v=' . $logoVersi;
  $fiturLogin = collect($identitas?->fitur_login ?: [
    'Manajemen Anggota',
    'Laporan Real-time',
    'Keamanan Terjamin',
    'Akses Multi-peran',
  ])->filter(fn ($item) => filled($item))->values();
  $statistikLogin = collect($identitas?->statistik_login ?: [
    ['nilai' => '500+', 'label' => 'Anggota Aktif'],
    ['nilai' => '50+', 'label' => 'Departemen'],
    ['nilai' => '99%', 'label' => 'Uptime'],
  ])->filter(function ($item) {
    return filled($item['nilai'] ?? null) || filled($item['label'] ?? null);
  })->values();
  $halamanLogin = request()->routeIs('login');
@endphp

<div class="isk-auth-wrapper">

  {{-- ========== LEFT PANEL: BRANDING ========== --}}
  <div class="isk-left-panel col-lg-7 d-none d-lg-flex">

    {{-- Decorative elements --}}
    <div class="isk-decor isk-decor-1"></div>
    <div class="isk-decor isk-decor-2"></div>
    <div class="isk-decor isk-decor-3"></div>
    <div class="isk-decor isk-decor-4"></div>
    <div class="isk-decor isk-decor-5"></div>

    {{-- Top: Platform Badge --}}
    <div class="position-relative" style="z-index:2">
      <div class="isk-brand-badge">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-right:.5rem">
          <circle cx="12" cy="12" r="4" fill="#a5b4fc"/>
          <circle cx="12" cy="12" r="9" stroke="#a5b4fc" stroke-width="1.5" stroke-dasharray="2 2"/>
        </svg>
        <span style="color:rgba(255,255,255,0.9);font-size:.8rem;font-weight:500;letter-spacing:.5px">
          {{ $singkatanAplikasi }} Platform &mdash; Versi {{ $versiAplikasi }}
        </span>
      </div>
    </div>

    {{-- Center: Hero Content --}}
    <div class="position-relative text-center" style="z-index:2">
      <div class="isk-hero-icon animate__animated animate__zoomIn" style="--animate-duration: 900ms;">
         <img src="{{ $logoAplikasiFinal }}" alt="Logo {{ $namaAplikasi }}"
             style="width:58px;height:58px;object-fit:cover;border-radius:12px"
           onerror="this.onerror=null;this.src='{{ $logoDefault }}';">
      </div>

      <h1 class="fw-bold mb-3 text-white" style="font-size:2.1rem;line-height:1.2;text-shadow:0 2px 16px rgba(0,0,0,0.25)">
        @if ($halamanLogin)
          <span
            data-typing-login
            data-typing-text="{{ $namaAplikasi }}"
            data-typing-speed="28"
            data-typing-delay="220"
            data-typing-caret="true"
          >{{ $namaAplikasi }}</span><br>
        @else
          {{ $namaAplikasi }}<br>
        @endif
        <span class="animate__animated animate__rubberBand" style="--animate-duration: 900ms; --animate-delay: 600ms;">
          {{ strtoupper($singkatanAplikasi) }}
        </span>
      </h1>
      <p class="mb-4 mx-auto" style="color:rgba(255,255,255,0.68);font-size:.95rem;max-width:400px;line-height:1.7">
        {{ $sloganAplikasi }}
      </p>

      {{-- Feature Pills --}}
      <div class="d-flex flex-wrap justify-content-center gap-2">
        @foreach ($fiturLogin as $fitur)
          <span class="isk-feature-pill">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" style="margin-right:.4rem">
              <path d="M20 6L9 17l-5-5" stroke="#a5b4fc" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            {{ $fitur }}
          </span>
        @endforeach
      </div>

      {{-- Stats --}}
      <div class="isk-stats-grid mt-4">
        @foreach ($statistikLogin as $stat)
          <div class="isk-stat-card">
            <div class="isk-stat-num">{{ $stat['nilai'] ?? '-' }}</div>
            <div class="isk-stat-label">{{ $stat['label'] ?? '-' }}</div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Bottom: Copyright --}}
    <div class="position-relative text-center" style="z-index:2">
      <p class="mb-0" style="color:rgba(255,255,255,0.38);font-size:.72rem">
        &copy; {{ date('Y') }} {{ $namaAplikasi }} &mdash; Hak cipta dilindungi.
      </p>
    </div>

  </div>
  {{-- ========== END LEFT PANEL ========== --}}

  {{-- ========== RIGHT PANEL: FORM ========== --}}
  <div class="isk-right-panel col-12 col-lg-5">
    <div class="isk-form-card">

      {{-- Mobile header --}}
      <div class="d-lg-none text-center mb-4">
        <div class="animate__animated animate__zoomIn" style="width:56px;height:56px;background:linear-gradient(135deg,var(--sio-main-color,#696cff),var(--sio-secondary-color,#8592a3));border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;box-shadow:0 4px 16px rgba(79,70,229,.35);--animate-duration: 850ms;">
          <img src="{{ $logoAplikasiFinal }}" alt="Logo {{ $namaAplikasi }}"
               style="width:30px;height:30px;object-fit:cover;border-radius:8px"
            onerror="this.onerror=null;this.src='{{ $logoDefault }}';">
        </div>
        @if ($halamanLogin)
          <div class="fw-bold mb-1" style="color:#1e293b;font-size:1rem;letter-spacing:.5px"
               data-typing-login
               data-typing-text="{{ strtoupper($singkatanAplikasi) }}"
               data-typing-speed="55"
               data-typing-delay="120"
               data-typing-caret="true">
            {{ strtoupper($singkatanAplikasi) }}
          </div>
          <div class="mb-0" style="color:#64748b;font-size:.8rem;font-weight:600;letter-spacing:.25px"
               data-typing-login
               data-typing-text="{{ $namaAplikasi }}"
               data-typing-speed="34"
               data-typing-delay="560"
               data-typing-caret="true">
            {{ $namaAplikasi }}
          </div>
        @else
          <h6 class="fw-bold mb-0" style="color:#1e293b">{{ $namaAplikasi }}</h6>
        @endif
      </div>

      {{-- Desktop mini-brand --}}
      <div class="d-none d-lg-flex align-items-center mb-4" style="gap:.65rem">
        <div class="isk-form-logo animate__animated animate__fadeInDown" style="--animate-duration: 700ms;">
          <img src="{{ $logoAplikasiFinal }}" alt="Logo {{ $namaAplikasi }}"
               style="width:24px;height:24px;object-fit:cover;border-radius:6px"
            onerror="this.onerror=null;this.src='{{ $logoDefault }}';">
        </div>
        <div>
          @if ($halamanLogin)
            <div style="font-size:.75rem;font-weight:700;color:var(--sio-main-color,#696cff);letter-spacing:.6px;line-height:1"
                 data-typing-login
                 data-typing-text="{{ strtoupper($singkatanAplikasi) }}"
                 data-typing-speed="55"
                 data-typing-delay="120"
                 data-typing-caret="true">
              {{ strtoupper($singkatanAplikasi) }}
            </div>
            <div style="font-size:.68rem;font-weight:600;color:var(--sio-secondary-color,#8592a3);letter-spacing:.4px"
                 data-typing-login
                 data-typing-text="{{ $namaAplikasi }}"
                 data-typing-speed="34"
                 data-typing-delay="560"
                 data-typing-caret="true">
              {{ $namaAplikasi }}
            </div>
          @else
            <div style="font-size:.75rem;font-weight:700;color:var(--sio-main-color,#696cff);letter-spacing:.6px;line-height:1">{{ strtoupper($singkatanAplikasi) }}</div>
            <div style="font-size:.68rem;font-weight:600;color:var(--sio-secondary-color,#8592a3);letter-spacing:.4px">{{ $namaAplikasi }}</div>
          @endif
        </div>
      </div>

      {{ $slot }}

    </div>
  </div>
  {{-- ========== END RIGHT PANEL ========== --}}

</div>

<script>
  function initLoginTypingEffect() {
    document.querySelectorAll('[data-typing-login]').forEach((el) => {
      if (el.dataset.typed === 'true') {
        return;
      }

      const text = el.getAttribute('data-typing-text') || el.textContent.trim();
      const speed = parseInt(el.getAttribute('data-typing-speed') || '35', 10);
      const delay = parseInt(el.getAttribute('data-typing-delay') || '0', 10);
      const withCaret = el.getAttribute('data-typing-caret') === 'true';

      el.dataset.typed = 'true';
      el.textContent = '';

      if (withCaret) {
        el.classList.add('isk-typing-caret');
      }

      window.setTimeout(() => {
        let index = 0;
        const timer = window.setInterval(() => {
          el.textContent += text.charAt(index);
          index += 1;

          if (index >= text.length) {
            window.clearInterval(timer);
          }
        }, speed);
      }, delay);
    });
  }

  document.addEventListener('DOMContentLoaded', initLoginTypingEffect);
  document.addEventListener('livewire:navigated', initLoginTypingEffect);
</script>
