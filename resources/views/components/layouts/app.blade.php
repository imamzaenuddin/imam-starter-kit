<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ config('app.layout') === 'horizontal' ? 'layout-navbar-fixed' : 'layout-menu-fixed' }}" data-base-url="{{url('/')}}" data-framework="laravel">
  <head>
    @include('partials.head')
  </head>

  <body>

    @if (config('app.layout') === 'horizontal')
      <div class="layout-wrapper layout-navbar-full layout-horizontal">
        <div class="layout-container">

          <!-- Navbar -->
          <x-layouts.navbar.default :title="$title ?? null"></x-layouts.navbar.default>
          <!--/ Navbar -->

          <!-- Layout container -->
          <div class="layout-page">
            <!-- Layout Content -->
            <x-layouts.menu.horizontal :title="$title ?? null"></x-layouts.menu.horizontal>
            <!--/ Layout Content -->

            <!-- Content wrapper -->
            <div class="content-wrapper">
              <!-- Content -->
              <div class="container-xxl flex-grow-1 container-p-y">
                {{ $slot }}
              </div>
              <!-- / Content -->

              <!-- Footer -->
              <x-layouts.footer.default :title="$title ?? null"></x-layouts.footer.default>
              <!--/ Footer -->
              <div class="content-backdrop fade"></div>
              <!-- / Content wrapper -->
            </div>
          </div>
          <!-- / Layout page -->
        </div>
      </div>
    @else
      <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

          <!-- Layout Content -->
          <x-layouts.menu.vertical :title="$title ?? null"></x-layouts.menu.vertical>
          <!--/ Layout Content -->

          <!-- Layout container -->
          <div class="layout-page">
            <!-- Navbar -->
            <x-layouts.navbar.default :title="$title ?? null"></x-layouts.navbar.default>
            <!--/ Navbar -->

            <!-- Content wrapper -->
            <div class="content-wrapper">
              <!-- Content -->
              <div class="container-xxl flex-grow-1 container-p-y">
                {{ $slot }}
              </div>
              <!-- / Content -->

              <!-- Footer -->
              <x-layouts.footer.default :title="$title ?? null"></x-layouts.footer.default>
              <!--/ Footer -->
              <div class="content-backdrop fade"></div>
              <!-- / Content wrapper -->
            </div>
          </div>
          <!-- / Layout page -->
        </div>
      </div>
    @endif

    <!-- Include Scripts -->
    @include('partials.scripts')
    <!-- / Include Scripts -->

    @if (auth()->check() && auth()->user()?->bisaMenu('/laporan/chat-ai', 'dapat_lihat'))
      <x-chat-ai-floating />
    @endif
  </body>
</html>
