@php
  $width = (int) ($width ?? 25);
  $logoDefault = asset('assets/img/identitas/gedung-default.svg');
  $identitas = app(\App\Services\IdentitasService::class)->aktif();
  $logoPath = trim((string) ($identitas?->logo_path ?? ''));
  $logoUrl = $logoDefault;

  if ($logoPath !== '') {
    if (\Illuminate\Support\Str::startsWith($logoPath, ['http://', 'https://'])) {
      $logoUrl = $logoPath;
    } elseif (\Illuminate\Support\Str::startsWith($logoPath, ['storage/', '/storage/'])) {
      $logoUrl = asset(ltrim($logoPath, '/'));
    } else {
      $logoUrl = asset('storage/' . ltrim($logoPath, '/'));
    }
  }
@endphp

<img src="{{ $logoUrl }}"
     alt="Logo {{ $identitas?->nama_aplikasi ?? 'Aplikasi' }}"
     style="width:{{ $width }}px;height:{{ $width }}px;object-fit:cover;border-radius:8px;"
     onerror="this.onerror=null;this.src='{{ $logoDefault }}';">
