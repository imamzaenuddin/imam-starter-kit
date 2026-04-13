
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700&family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" integrity="sha512-b7t+nKfMLY5G3L98R0K1C7QxV2sWi4KFOeFHrgb3R04QLbTjaC4pzbO0MJdHj7FVGHvXZHzVvzJeY9q24apqYw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

@php
		$identitasStyle = app(\App\Services\IdentitasService::class)->aktif();
		$mainColor = $identitasStyle?->main_color ?: '#696cff';
		$secondaryColor = $identitasStyle?->secondary_color ?: '#8592a3';
@endphp

<style>
	:root {
		--sio-main-color: {{ $mainColor }};
		--sio-secondary-color: {{ $secondaryColor }};
		--bs-primary: var(--sio-main-color);
		--bs-secondary: var(--sio-secondary-color);
		--sio-font-sans: 'Plus Jakarta Sans', 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
	}

	html,
	body,
	.layout-wrapper,
	.menu-vertical,
	.navbar,
	.card,
	.btn,
	.form-control,
	.form-select {
		font-family: var(--sio-font-sans) !important;
		text-rendering: optimizeLegibility;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
		font-feature-settings: 'kern' 1;
	}

	.text-primary,
	.menu-vertical .menu-link,
	a {
		color: inherit;
	}

	.btn-primary,
	.bg-primary {
		background-color: var(--sio-main-color) !important;
		border-color: var(--sio-main-color) !important;
	}

	.btn-outline-primary {
		color: var(--sio-main-color) !important;
		border-color: var(--sio-main-color) !important;
	}

	.btn-outline-primary:hover {
		background-color: var(--sio-main-color) !important;
		color: #fff !important;
	}

	.bg-label-primary {
		background-color: color-mix(in srgb, var(--sio-main-color) 16%, #ffffff) !important;
		color: var(--sio-main-color) !important;
	}

	.bg-label-secondary {
		background-color: color-mix(in srgb, var(--sio-secondary-color) 16%, #ffffff) !important;
		color: var(--sio-secondary-color) !important;
	}

	.menu-vertical .menu-item.active > .menu-link,
	.menu-vertical .menu-item.open > .menu-link,
	.menu-vertical .menu-item .menu-link:hover {
		color: var(--sio-main-color) !important;
	}

	.menu-vertical .menu-item.active:not(.open) > .menu-link {
		background-color: color-mix(in srgb, var(--sio-main-color) 14%, #ffffff) !important;
	}

	/* Sidebar menu panjang: aktifkan scroll vertikal pada daftar menu */
	.layout-menu {
		height: 100vh;
		display: flex;
		flex-direction: column;
	}

	.layout-menu .app-brand,
	.layout-menu .menu-inner-shadow {
		flex: 0 0 auto;
	}

	.layout-menu .menu-inner {
		flex: 1 1 auto;
		overflow-y: auto;
		overflow-x: hidden;
		min-height: 0;
	}

	.layout-menu .menu-inner::-webkit-scrollbar {
		width: 8px;
	}

	.layout-menu .menu-inner::-webkit-scrollbar-thumb {
		background-color: color-mix(in srgb, var(--sio-secondary-color) 45%, #ffffff);
		border-radius: 999px;
	}

	.layout-menu .menu-inner::-webkit-scrollbar-track {
		background: transparent;
	}

	.form-check-input:checked {
		background-color: var(--sio-main-color) !important;
		border-color: var(--sio-main-color) !important;
	}

	a:hover {
		color: var(--sio-main-color) !important;
	}
</style>

@vite(['resources/assets/vendor/fonts/iconify/iconify.js'])

<!-- Core CSS -->
@vite(['resources/assets/vendor/scss/core.scss','resources/assets/css/demo.css', 'resources/css/app.css'])

<!-- Vendor Styles -->
@yield('vendor-style')

<!-- Page Styles -->
@yield('page-style')
