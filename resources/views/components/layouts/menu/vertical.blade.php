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

    // Render menu item secara rekursif
    $renderMenuItem = function (\App\Models\Menu $menu, bool $isSub = false) use (&$renderMenuItem, &$isAktif, &$terjemahMenu, &$renderIcon) {
        $aktif = $isAktif($menu);
        $hasChildren = $menu->children->isNotEmpty();

        $itemClass = 'menu-item';
        if ($hasChildren) {
            if ($aktif) {
                $itemClass .= ' active open';
            }
        } else {
            if ($aktif) {
                $itemClass .= ' active';
            }
        }

        $linkClass = 'menu-link';
        if ($hasChildren) {
            $linkClass .= ' menu-toggle';
        }

        $href = $hasChildren ? 'javascript:void(0);' : ($menu->url ? url($menu->url) : 'javascript:void(0);');
        $wireNavigate = (!$hasChildren && $menu->url) ? 'wire:navigate' : '';

        $iconHtml = '';
        if ($menu->icon) {
            $iconHtml = '<i class="' . $renderIcon($menu->icon) . '"></i>';
        } elseif (!$isSub) {
            // Icon default untuk menu root jika tidak ada icon
            $iconHtml = '<i class="menu-icon tf-icons bx bx-layer"></i>';
        }

        $output = '<li class="' . $itemClass . '">';
        $output .= '<a href="' . $href . '" class="' . $linkClass . '" ' . $wireNavigate . '>';
        $output .= $iconHtml;
        $output .= '<div class="text-truncate">' . e($terjemahMenu($menu->nama)) . '</div>';
        $output .= '</a>';

        if ($hasChildren) {
            $output .= '<ul class="menu-sub">';
            foreach ($menu->children as $child) {
                $output .= $renderMenuItem($child, true);
            }
            $output .= '</ul>';
        }

        $output .= '</li>';

        return $output;
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
      {!! $renderMenuItem($menu) !!}
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
  // Gunakan event delegation agar toggle tetap bekerja pada wire:navigate dan menu bersarang baru
  if (!window.menuToggleListenerAdded) {
    document.addEventListener('click', function(e) {
      const menuToggle = e.target.closest('.menu-toggle');
      if (menuToggle) {
        e.preventDefault();
        const menuItem = menuToggle.closest('.menu-item');
        if (menuItem) {
          menuItem.classList.toggle('open');
        }
      }
    });
    window.menuToggleListenerAdded = true;
  }
</script>

