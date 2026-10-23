<x-guest-layout>
    <h1 class="bp-login-heading">Selamat Datang</h1>
    <p class="bp-login-subheading">
        Masuk untuk mencatat aktivitas harian dan memantau progres tim IT.
    </p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="bp-login-label">Alamat email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                class="bp-login-input"
                placeholder="nama@borneoprima.com"
            >
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-rose-600" />
        </div>

        <div>
            <label for="password" class="bp-login-label">Kata sandi</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="bp-login-input"
                placeholder="Masukkan kata sandi"
            >
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-rose-600" />
        </div>

        <div class="flex items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    class="h-4 w-4 rounded border-slate-300 text-brand accent-brand focus:ring-brand"
                >
                <span>Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-brand hover:text-brand-hover">
                    Lupa sandi?
                </a>
            @endif
        </div>

        <button type="submit" class="bp-login-submit">
            Masuk ke Dashboard
        </button>
    </form>
</x-guest-layout>
