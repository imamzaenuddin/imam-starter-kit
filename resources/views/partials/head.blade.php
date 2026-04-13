<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />


@props([
    'pageTitle',
])

@php
  $identitas = app(\App\Services\IdentitasService::class)->aktif();
  $namaAplikasi = $identitas?->nama_aplikasi ?? 'Sistem Informasi Organisasi';
  $faviconUrl = $identitas?->logo_path ? asset('storage/' . ltrim($identitas->logo_path, '/')) : asset('assets/img/identitas/gedung-default.svg');
@endphp


<title>@yield('title') |
  {{ $namaAplikasi }} -
  {{ config('variables.templateSuffix') ? config('variables.templateSuffix') : 'TemplateSuffix' }}
</title>

<meta name="description" content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
<meta name="keywords" content="{{ config('variables.templateKeyword') ? config('variables.templateKeyword') : '' }}">
<!-- laravel CRUD token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Canonical SEO -->
<meta property="og:title" content="{{ config('variables.templateName') ? config('variables.templateName') : '' }}{{ config('variables.ogTitle') ? config('variables.ogTitle') : '' }}" />
<meta property="og:type" content="{{ config('variables.ogType') ? config('variables.ogType') : '' }}" />
<meta property="og:url" content="{{ config('variables.productPage') ? config('variables.productPage') : '' }}" />
<meta property="og:image" content="{{ config('variables.ogImage') ? config('variables.ogImage') : '' }}" />
<meta property="og:description" content="{{ config('variables.templateName') ? config('variables.templateName') : '' }}{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
<meta property="og:site_name" content="{{ config('variables.creatorName') ? config('variables.creatorName') : '' }}" />
<link rel="canonical" href="{{ config('variables.productPage') ? config('variables.productPage') : '' }}">
<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="{{ $faviconUrl }}" />


<!-- Include Styles -->
@include('partials.styles')

@livewireStyles
