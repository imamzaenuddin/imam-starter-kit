<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="layout-menu-fixed" data-base-url="{{url('/')}}" data-framework="laravel">
  @section('title', __('messages.welcome_page_title'))
  <head>
    @include('partials.head')
  </head>
  <body>
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-end">
          @if (Route::has('login'))
            @auth
              <a href="{{ url('/dashboard') }}" class="btn btn-primary">{{ __('messages.dashboard') }}</a>
            @else
              <a href="{{ route('login') }}" class="btn btn-secondary me-2">{{ __('messages.log_in') }}</a>

                @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn btn-primary">{{ __('messages.register_now') }}</a>
                @endif
            @endauth
          @endif
      </div>
      <div class="position-absolute top-50 start-50 translate-middle">
        <div class="card">
          <div class="row g-0">
            <div class="col-md-6 d-flex align-items-center">
              <div class="card-body">
                <h1 class="h4 card-title">{{ __('messages.welcome_card_title') }}</h1>
                <p class="card-text mb-5">{{ __('messages.welcome_card_subtitle') }}</p>
                <ul class="mb-0">
                  <li class="mb-3">{{ __('messages.read_laravel_docs') }} <a href="https://laravel.com/docs" target="_blank">{{ __('messages.documentation') }}</a></li>
                  <li class="mb-3">{{ __('messages.read_sneat_docs') }} <a href="{{ config('variables.documentation') }}" target="_blank">{{ __('messages.documentation') }}</a></li>
                  <li>{{ __('messages.sneat_components') }} <a href="{{ config('variables.repository') }}" target="_blank">{{ __('messages.components') }}</a></li>
                </ul>
              </div>
            </div>
            <div class="col-md-6">
              <img class="card-img card-img-right" src="{{asset('assets/img/illustrations/laravel-livewire-sneat.png')}}" alt="imam-starter-kit preview">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Include Scripts -->
    @include('partials.scripts')
    <!-- / Include Scripts -->
  </body>
</html>
