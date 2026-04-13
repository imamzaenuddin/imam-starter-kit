<!-- Footer-->
@php
  $identitas = app(\App\Services\IdentitasService::class)->aktif();
@endphp
<footer class="content-footer footer bg-footer-theme">
  <div class="container-xxl">
    <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
      <div class="text-body">
        © <?php echo date('Y'); ?>,
        {{ $identitas?->footer_text ?? 'Sistem Informasi Organisasi' }}
        @if ($identitas?->versi)
          <span class="text-muted">v{{ $identitas->versi }}</span>
        @endif
      </div>
    </div>
  </div>
</footer>
<!--/ Footer-->
