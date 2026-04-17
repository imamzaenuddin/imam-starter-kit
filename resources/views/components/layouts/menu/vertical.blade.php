@php
    /** @var \Illuminate\Database\Eloquent\Collection $menus */
    $menus = app(\App\Services\MenuService::class)->menuTersedia();
  $renderIcon = fn (?string $icon, string $kelasTambahan = 'menu-icon tf-icons') => \App\Models\Menu::classIconRender($icon, $kelasTambahan);

    /**
     * Helper: tentukan apakah suatu menu (atau child-nya) sedang aktif.
     * Digunakan untuk highlight dan auto-expand sub menu.
     */
    $isAktif = function (\App\Models\Menu $menu) use (&$isAktif): bool {
        if ($menu->url && request()->is(ltrim($menu->url, '/'))) {
            return true;
        }
        foreach ($menu->children as $child) {
            if ($isAktif($child)) return true;
        }
        return false;
    };

      // Coba terjemahkan nama menu dinamis; fallback ke nama asli jika key belum ada.
      $terjemahMenu = function (string $namaMenu): string {
        $key = 'messages.menu_' . \Illuminate\Support\Str::slug($namaMenu, '_');
        $hasil = __($key);

        return str_starts_with($hasil, 'messages.menu_') ? $namaMenu : $hasil;
      };
@endphp

<!-- Menu -->
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo">
    <a href="{{ url('/') }}" class="app-brand-link"><x-app-logo /></a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">

    {{-- =============================================
         MENU STATIS: Dashboard & Settings (selalu ada)
         ============================================= --}}
    <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <a class="menu-link" href="{{ route('dashboard') }}" wire:navigate>
        <i class="menu-icon tf-icons bx bx-home-circle"></i>
        <div class="text-truncate">{{ __('messages.dashboard') }}</div>
      </a>
    </li>

    {{-- =============================================
         MENU DINAMIS: Diambil dari DB sesuai level user
         ============================================= --}}
    @foreach ($menus as $menu)
      @php $aktif = $isAktif($menu); @endphp

      @if ($menu->children->isNotEmpty())
        {{-- Menu dengan sub-menu --}}
        <li class="menu-item {{ $aktif ? 'active open' : '' }}">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            @if ($menu->icon)
              <i class="{{ $renderIcon($menu->icon) }}"></i>
            @else
              <i class="menu-icon tf-icons bx bx-layer"></i>
            @endif
            <div class="text-truncate">{{ $terjemahMenu($menu->nama) }}</div>
          </a>
          <ul class="menu-sub">
            @foreach ($menu->children as $child)
              @php $childAktif = $child->url && request()->is(ltrim($child->url, '/')); @endphp
              <li class="menu-item {{ $childAktif ? 'active' : '' }}">
                <a class="menu-link"
                   href="{{ $child->url ? url($child->url) : 'javascript:void(0);' }}"
                   {{ $child->url ? 'wire:navigate' : '' }}>
                  @if ($child->icon)
                    <i class="{{ $renderIcon($child->icon) }}"></i>
                  @endif
                  <div class="text-truncate">{{ $terjemahMenu($child->nama) }}</div>
                </a>
              </li>
            @endforeach
          </ul>
        </li>

      @else
        {{-- Menu tunggal tanpa sub-menu --}}
        <li class="menu-item {{ $aktif ? 'active' : '' }}">
          <a class="menu-link"
             href="{{ $menu->url ? url($menu->url) : 'javascript:void(0);' }}"
             {{ $menu->url ? 'wire:navigate' : '' }}>
            @if ($menu->icon)
              <i class="{{ $renderIcon($menu->icon) }}"></i>
            @else
              <i class="menu-icon tf-icons bx bx-circle"></i>
            @endif
            <div class="text-truncate">{{ $terjemahMenu($menu->nama) }}</div>
          </a>
        </li>
      @endif
    @endforeach

    {{-- =============================================
         MENU STATIS: Settings (selalu ada)
         ============================================= --}}
    <li class="menu-item {{ request()->is('settings/*') ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-cog"></i>
        <div class="text-truncate">{{ __('messages.settings') }}</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->routeIs('settings.profile') ? 'active' : '' }}">
          <a class="menu-link" href="{{ route('settings.profile') }}" wire:navigate>{{ __('messages.profile') }}</a>
        </li>
        <li class="menu-item {{ request()->routeIs('settings.password') ? 'active' : '' }}">
          <a class="menu-link" href="{{ route('settings.password') }}" wire:navigate>{{ __('messages.password') }}</a>
        </li>
        @if (auth()->user()?->isSuperadmin())
          <li class="menu-item {{ request()->routeIs('settings.two-factor') ? 'active' : '' }}">
            <a class="menu-link" href="{{ route('settings.two-factor') }}" wire:navigate>{{ __('messages.two_factor_settings_menu') }}</a>
          </li>
        @endif
      </ul>
    </li>

  </ul>
</aside>
<!-- / Menu -->

<script>
  document.querySelectorAll('.menu-toggle').forEach(function(menuToggle) {
    menuToggle.addEventListener('click', function() {
      const menuItem = menuToggle.closest('.menu-item');
      menuItem.classList.toggle('open');
    });
  });
</script>

