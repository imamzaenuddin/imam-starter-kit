@php
	$identitas = app(\App\Services\IdentitasService::class)->aktif();
	$namaAplikasi = $identitas?->nama_aplikasi ?? 'ISK';
	$labelBrand = $identitas?->singkatan_aplikasi ?: $namaAplikasi;
@endphp
<span class="app-brand-logo demo"><x-app-logo-icon /></span>
<span class="app-brand-text demo menu-text fw-bold ms-2 text-truncate"
	  style="max-width: 170px;"
	  title="{{ $namaAplikasi }}">
	{{ $labelBrand }}
</span>
