<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <div class="flex">
                @if(Auth::check() && Auth::user()->role === 'admin')
                    <div class="shrink-0 flex items-center">
                        <a href="{{ route('dashboard') }}" class="font-bold text-xl text-blue-600">AdminPanel</a>
                    </div>
                @elseif(Auth::check() && Auth::user()->role === 'owner')
                    <div class="shrink-0 flex items-center">
                        <a href="{{ route('owner.dashboard') }}" class="font-bold text-xl text-orange-600">MitraPanel</a>
                    </div>
                @else
                    <div class="shrink-0 flex items-center">
                        <a href="{{ route('landing') }}" class="flex items-center gap-2 hover:opacity-80 transition">
                            <div class="bg-green-500 text-white p-1.5 rounded-lg shadow-sm">
                                <i class="fa-solid fa-map-location-dot"></i>
                            </div>
                            <span class="font-bold text-xl text-gray-900">Tambal<span class="text-green-600">Finder</span></span>
                        </a>
                    </div>

                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <x-nav-link :href="route('landing')" :active="request()->routeIs('landing')">
                            <i class="fa-solid fa-house mr-2"></i> {{ __('Home') }}
                        </x-nav-link>

                        <x-nav-link :href="route('peta.index')"
                                    :active="request()->routeIs('peta.index') || request()->routeIs('booking.create')">
                            <i class="fa-solid fa-map-location mr-2"></i> {{ __('Cari Bengkel') }}
                        </x-nav-link>

                        <x-nav-link :href="route('booking.history')"
                                    :active="request()->routeIs('booking.history') || request()->routeIs('booking.show')">
                            <i class="fa-solid fa-clock-rotate-left mr-2"></i> {{ __('Riwayat') }}
                        </x-nav-link>
                    </div>
                @endif
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-full text-gray-500 bg-gray-50 hover:text-gray-700 transition gap-2">
                            <div class="h-8 w-8 rounded-full bg-green-600 flex items-center justify-center font-bold text-white">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            {{ Auth::user()->name }}
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-t border-gray-100">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('landing')" :active="request()->routeIs('landing')">Home</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('peta.index')" :active="request()->routeIs('peta.index')">Cari Bengkel</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('booking.history')" :active="request()->routeIs('booking.history')">Riwayat</x-responsive-nav-link>
        </div>
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4 font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
            <div class="mt-3 space-y-1">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
