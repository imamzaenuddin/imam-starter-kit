<nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
    id="layout-navbar">
    @php
        $bahasaAktif = \App\Models\Bahasa::query()
            ->where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('kode')
            ->get();

        $pathBendera = function (string $kode): string {
            $locale = strtolower(str_replace('_', '-', $kode));

            return match ($locale) {
                'id', 'id-id' => asset('assets/img/flags/id.svg'),
                'en', 'en-us', 'en-gb' => asset('assets/img/flags/en.svg'),
                default => asset('assets/img/flags/default.svg'),
            };
        };

        $kodeAktif = app()->getLocale();
    @endphp
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
            <i class="icon-base bx bx-menu icon-md"></i>
        </a>
    </div>

    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>

    <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
        <!-- Search -->
        <div class="navbar-nav align-items-center me-auto">
            <div class="nav-item d-flex align-items-center">
                <span class="w-px-22 h-px-22"><i class="icon-base bx bx-search icon-md"></i></span>
                <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2 d-md-block d-none"
                    placeholder="{{ __('messages.search_placeholder') }}" aria-label="{{ __('messages.search_placeholder') }}" />
            </div>
        </div>
        <!-- /Search -->

        <ul class="navbar-nav flex-row align-items-center ms-md-auto">
            <li class="nav-item dropdown me-3">
                <a class="nav-link dropdown-toggle" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <img src="{{ $pathBendera($kodeAktif) }}" alt="Flag {{ strtoupper($kodeAktif) }}" class="rounded me-1" style="width:20px;height:14px;object-fit:cover;border:1px solid rgba(0,0,0,.12)">
                    <span class="d-none d-md-inline">{{ strtoupper(app()->getLocale()) }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    @forelse ($bahasaAktif as $bahasa)
                        <li>
                            <form method="POST" action="{{ route('bahasa.ganti') }}">
                                @csrf
                                <input type="hidden" name="kode" value="{{ $bahasa->kode }}">
                                <button type="submit" class="dropdown-item d-flex justify-content-between align-items-center {{ app()->getLocale() === $bahasa->kode ? 'active' : '' }}">
                                    <span class="d-flex align-items-center gap-2">
                                        <img src="{{ $pathBendera($bahasa->kode) }}" alt="Flag {{ strtoupper($bahasa->kode) }}" class="rounded" style="width:20px;height:14px;object-fit:cover;border:1px solid rgba(0,0,0,.12)">
                                        <span>{{ $bahasa->nama_native ?: $bahasa->nama }}</span>
                                    </span>
                                    <small class="text-uppercase text-muted">{{ $bahasa->kode }}</small>
                                </button>
                            </form>
                        </li>
                    @empty
                        <li><span class="dropdown-item text-muted">{{ __('messages.language_empty') }}</span></li>
                    @endforelse
                </ul>
            </li>
            <li class="nav-item lh-1 me-4">
                <a class="github-button"
                    href="{{ config('variables.repository') }}"
                    data-icon="octicon-star" data-size="large" data-show-count="true"
                    aria-label="{{ __('messages.star_on_github_aria') }}">{{ __('messages.star') }}</a>
            </li>
            <!-- User -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <!-- Check if the user is authenticated -->
                @if (Auth::check())
                    <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);"
                        data-bs-toggle="dropdown">
                        <div class="avatar avatar-online">
                            <img src="{{ Auth::user()->profile_photo_url ?? asset('assets/img/avatars/1.png') }}" alt
                                class="w-px-40 h-auto rounded-circle">
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('settings.profile') }}" wire:navigate>
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar avatar-online">
                                            <img src="{{ Auth::user()->profile_photo_url ?? asset('assets/img/avatars/1.png') }}"
                                                alt class="w-px-40 h-auto rounded-circle" />
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                                        <small class="text-body-secondary">{{ Auth::user()->role ?? __('messages.user') }}</small>
                                        <!-- Display user role -->
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider my-1"></div>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('settings.profile') ? 'active' : '' }}"
                                href="{{ route('settings.profile') }}" wire:navigate>
                                <i class="icon-base bx bx-user icon-md me-3"></i><span>{{ __('messages.my_profile') }}</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('settings.password') ? 'active' : '' }}"
                                href="{{ route('settings.password') }}" wire:navigate>
                                <i class="icon-base bx bx-cog icon-md me-3"></i><span>{{ __('messages.settings') }}</span>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider my-1"></div>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item btn p-0" type="submit"><i
                                        class="icon-base bx bx-power-off icon-md me-3"></i><span>{{ __('messages.log_out') }}</span></button>
                            </form>
                        </li>
                    </ul>
                @else
                    <!-- If the user is not logged in, show the login option -->
                    <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);"
                        data-bs-toggle="dropdown">
                        <div class="avatar avatar-online">
                            <img src="{{ asset('assets/img/avatars/1.png') }}" alt
                                class="w-px-40 h-auto rounded-circle" />
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('login') }}">{{ __('messages.log_in') }}</a></li>
                    </ul>
                @endif
            </li>
            <!--/ User -->
        </ul>
    </div>
</nav>
