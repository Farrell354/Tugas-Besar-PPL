<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-xl font-bold text-gray-800">Masuk ke Akun</h2>
        <p class="text-sm text-gray-500 mt-1">Selamat datang kembali!</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input id="email" class="block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm sm:text-sm px-4 py-2.5"
                   type="email" name="email" :value="old('email')" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <div class="flex justify-between items-center mb-1">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-xs text-blue-600 hover:text-blue-800 font-medium hover:underline" href="{{ route('password.request') }}">
                        Lupa password?
                    </a>
                @endif
            </div>
            <input id="password" class="block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm sm:text-sm px-4 py-2.5"
                   type="password" name="password" required autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="block">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
            </label>
        </div>

        <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
            Masuk Sekarang
        </button>

        <div class="mt-4 text-center">
            <p class="text-sm text-gray-600">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-bold text-blue-600 hover:text-blue-500 hover:underline">
                    Daftar disini
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
