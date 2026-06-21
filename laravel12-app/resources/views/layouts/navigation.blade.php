<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <span class="text-xl font-bold text-gray-800">SIAP-SMK</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-1 sm:-my-px sm:ms-10 sm:flex">
                    <!-- Dashboard -->
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <!-- Surat Masuk -->
                    @if(auth()->user()->can('surat_masuk.view.any') || auth()->user()->hasRole(['admin', 'staff_tu', 'pimpinan', 'kabag']))
                    <x-nav-link :href="route('surat-masuk.index')" :active="request()->routeIs('surat-masuk.*')">
                        {{ __('Surat Masuk') }}
                    </x-nav-link>
                    @endif

                    <!-- Surat Keluar -->
                    @if(auth()->user()->can('surat_keluar.view.any') || auth()->user()->hasRole(['admin', 'staff_tu', 'kabag']))
                    <x-nav-link :href="route('surat-keluar.index')" :active="request()->routeIs('surat-keluar.*')">
                        {{ __('Surat Keluar') }}
                    </x-nav-link>
                    @endif

                    <!-- Disposisi -->
                    @if(auth()->user()->can('disposisi.view.any') || auth()->user()->hasRole(['admin', 'staff_tu', 'pimpinan', 'kabag']))
                    <x-nav-link :href="route('disposisi.saya')" :active="request()->routeIs('disposisi.*') && !request()->routeIs('disposisi.template.*')">
                        {{ __('Disposisi') }}
                    </x-nav-link>
                    @endif

                    <!-- Arsip -->
                    @if(auth()->user()->can('arsip.view.any') || auth()->user()->hasRole(['admin', 'staff_tu']))
                    <x-nav-link :href="route('arsip.index')" :active="request()->routeIs('arsip.*')">
                        {{ __('Arsip') }}
                    </x-nav-link>
                    @endif

                    <!-- Reports Dropdown -->
                    @if(auth()->user()->can('reports.view.any') || auth()->user()->hasRole(['admin', 'staff_tu', 'pimpinan']))
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150 {{ request()->routeIs('reports.*') ? 'text-gray-700 bg-gray-100' : '' }}">
                                <div>{{ __('Laporan') }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('reports.index')">
                                {{ __('Statistik') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('reports.buku-agenda.masuk')">
                                {{ __('Buku Agenda Masuk') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('reports.buku-agenda.keluar')">
                                {{ __('Buku Agenda Keluar') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('reports.rekap-disposisi')">
                                {{ __('Rekap Disposisi') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('reports.arsip-jatuh-tempo')">
                                {{ __('Arsip Jatuh Tempo') }}
                            </x-dropdown-link>
                            @if(auth()->user()->hasRole(['admin', 'staff_tu']))
                            <x-dropdown-link :href="route('reports.audit-trail')">
                                {{ __('Audit Trail') }}
                            </x-dropdown-link>
                            @endif
                        </x-slot>
                    </x-dropdown>
                    @endif

                    <!-- Master Data Dropdown (Admin & Staff TU only) -->
                    @if(auth()->user()->hasRole(['admin', 'staff_tu']))
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150 {{ request()->routeIs('master.*') ? 'text-gray-700 bg-gray-100' : '' }}">
                                <div>{{ __('Master Data') }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('master.units.index')">
                                {{ __('Unit Kerja') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('master.klasifikasi-arsip.index')">
                                {{ __('Klasifikasi Arsip') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('master.sifat-surat.index')">
                                {{ __('Sifat Surat') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('disposisi.template.index')">
                                {{ __('Template Disposisi') }}
                            </x-dropdown-link>
                            @if(auth()->user()->hasRole('admin'))
                            <x-dropdown-link :href="route('admin.delegasi.index')">
                                {{ __('Delegasi') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.import.index')">
                                {{ __('Import Data') }}
                            </x-dropdown-link>
                            @endif
                        </x-slot>
                    </x-dropdown>
                    @endif

                    <!-- Admin System Dropdown (Admin only) -->
                    @if(auth()->user()->hasRole('admin'))
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150 {{ request()->routeIs('admin.*') && !request()->routeIs('admin.delegasi.*') && !request()->routeIs('admin.import.*') ? 'text-gray-700 bg-gray-100' : '' }}">
                                <div>{{ __('System') }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('admin.audit-trail')">
                                {{ __('Audit Trail') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.error-logs')">
                                {{ __('Error Logs') }}
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                    @endif
                </div>
            </div>

            <!-- Right Side: Search, Notifications, Settings -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">
                <!-- Global Search Bar -->
                <div class="relative">
                    <input 
                        type="text" 
                        id="global-search"
                        placeholder="Cari surat... (Ctrl+K)"
                        class="w-64 px-4 py-2 pr-10 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @keydown.ctrl.k.window.prevent="$refs.globalSearchInput.focus()"
                        ref="globalSearchInput"
                    >
                    <button 
                        onclick="document.getElementById('global-search-form').submit()"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                    <form id="global-search-form" action="{{ route('search.index') }}" method="GET" class="hidden">
                        <input type="hidden" name="q" value="" id="global-search-value">
                    </form>
                </div>

                <!-- Notification Bell -->
                @php
                    $notifications = auth()->user()->notifications()->latest()->take(5)->get();
                    $unreadCount = auth()->user()->unreadNotifications()->count();
                @endphp
                <x-notification-bell :notifications="$notifications" :unreadCount="$unreadCount" />

                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if(auth()->user()->can('surat_masuk.view.any') || auth()->user()->hasRole(['admin', 'staff_tu', 'pimpinan', 'kabag']))
            <x-responsive-nav-link :href="route('surat-masuk.index')" :active="request()->routeIs('surat-masuk.*')">
                {{ __('Surat Masuk') }}
            </x-responsive-nav-link>
            @endif

            @if(auth()->user()->can('surat_keluar.view.any') || auth()->user()->hasRole(['admin', 'staff_tu', 'kabag']))
            <x-responsive-nav-link :href="route('surat-keluar.index')" :active="request()->routeIs('surat-keluar.*')">
                {{ __('Surat Keluar') }}
            </x-responsive-nav-link>
            @endif

            @if(auth()->user()->can('disposisi.view.any') || auth()->user()->hasRole(['admin', 'staff_tu', 'pimpinan', 'kabag']))
            <x-responsive-nav-link :href="route('disposisi.saya')" :active="request()->routeIs('disposisi.*') && !request()->routeIs('disposisi.template.*')">
                {{ __('Disposisi') }}
            </x-responsive-nav-link>
            @endif

            @if(auth()->user()->can('arsip.view.any') || auth()->user()->hasRole(['admin', 'staff_tu']))
            <x-responsive-nav-link :href="route('arsip.index')" :active="request()->routeIs('arsip.*')">
                {{ __('Arsip') }}
            </x-responsive-nav-link>
            @endif

            @if(auth()->user()->can('reports.view.any') || auth()->user()->hasRole(['admin', 'staff_tu', 'pimpinan']))
            <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                {{ __('Laporan') }}
            </x-responsive-nav-link>
            @endif

            @if(auth()->user()->hasRole(['admin', 'staff_tu']))
            <x-responsive-nav-link :href="route('master.units.index')" :active="request()->routeIs('master.*')">
                {{ __('Master Data') }}
            </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Search -->
        <div class="pt-4 pb-2 px-4">
            <form action="{{ route('search.index') }}" method="GET">
                <input 
                    type="text" 
                    name="q"
                    placeholder="Cari surat..."
                    class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </form>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<!-- Global Search Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('global-search');
    const searchValue = document.getElementById('global-search-value');
    const searchForm = document.getElementById('global-search-form');

    if (searchInput && searchValue && searchForm) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchValue.value = searchInput.value;
                searchForm.submit();
            }
        });
    }

    // Notification polling
    function fetchUnreadCount() {
        fetch('{{ route("notifications.unread-count") }}', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.notification-badge');
            if (badge && data.count > 0) {
                badge.textContent = data.count > 99 ? '99+' : data.count;
                badge.style.display = 'inline-flex';
            } else if (badge) {
                badge.style.display = 'none';
            }
        })
        .catch(console.error);
    }

    // Poll every 30 seconds
    setInterval(fetchUnreadCount, 30000);
    fetchUnreadCount();
});
</script>
