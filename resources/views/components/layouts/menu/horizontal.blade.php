@php
    /** @var \Illuminate\Database\Eloquent\Collection $menus */
    $menus = app(\App\Services\MenuService::class)->menuTersedia();
    $renderIcon = fn (?string $icon, string $kelasTambahan = 'menu-icon tf-icons') => \App\Models\Menu::classIconRender($icon, $kelasTambahan);

    /**
     * Helper: tentukan apakah suatu menu (atau child-nya) sedang aktif.
     * Digunakan untuk highlight.
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
        if ($aktif) {
            $itemClass .= ' active';
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
        $output .= '<div>' . e($terjemahMenu($menu->nama)) . '</div>';
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

<!-- Horizontal Menu -->
<aside id="layout-menu" class="layout-menu-horizontal menu-horizontal menu bg-menu-theme flex-grow-0">
  <div class="container-xxl d-flex h-100">
    <ul class="menu-inner">

      {{-- =============================================
           MENU STATIS: Dashboard
           ============================================= --}}
      <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <a class="menu-link" href="{{ route('dashboard') }}" wire:navigate>
          <i class="menu-icon tf-icons bx bx-home-circle"></i>
          <div>{{ __('messages.dashboard') }}</div>
        </a>
      </li>

      {{-- =============================================
           MENU DINAMIS: Diambil dari DB sesuai level user
           ============================================= --}}
      @foreach ($menus as $menu)
        {!! $renderMenuItem($menu) !!}
      @endforeach

      {{-- =============================================
           MENU STATIS: Settings
           ============================================= --}}
      <li class="menu-item {{ request()->is('settings/*') ? 'active' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons bx bx-cog"></i>
          <div>{{ __('messages.settings') }}</div>
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
  </div>
</aside>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    initializeHorizontalMenu();
  });

  document.addEventListener('livewire:navigated', function() {
    initializeHorizontalMenu();
  });

  function initializeHorizontalMenu() {
    document.querySelectorAll('.menu-horizontal .menu-toggle').forEach(function(menuToggle) {
      // Remove old listeners to avoid multiple binding on page refreshes/livewire updates
      menuToggle.outerHTML = menuToggle.outerHTML;
    });

    document.querySelectorAll('.menu-horizontal .menu-toggle').forEach(function(menuToggle) {
      menuToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const menuItem = menuToggle.closest('.menu-item');
        
        // Close other open horizontal submenus (except ancestor items)
        document.querySelectorAll('.menu-horizontal .menu-item.open').forEach(function(openItem) {
          if (openItem !== menuItem && !openItem.contains(menuItem)) {
            openItem.classList.remove('open');
          }
        });
        
        menuItem.classList.toggle('open');
      });
    });

    document.addEventListener('click', function(e) {
      if (!e.target.closest('.menu-horizontal')) {
        document.querySelectorAll('.menu-horizontal .menu-item.open').forEach(function(openItem) {
          openItem.classList.remove('open');
        });
      }
    });
  }
</script>
