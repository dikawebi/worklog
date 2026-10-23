<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'IT Worklog') }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-bp.svg') }}">
        <link rel="alternate icon" href="{{ asset('images/logo-bp.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <div class="bp-login">
            <div class="bp-login-form-col">
                <div class="bp-login-form-inner">
                    <a href="{{ url('/') }}" class="bp-login-brand">
                        <span class="bp-login-brand-row">
                            <img src="{{ asset('images/logo-bp.svg') }}" alt="PT Borneo Prima" class="bp-login-brand-logo">
                            <span class="bp-login-brand-wordmark">
                                <span class="bp-login-brand-name">PT BORNEO PRIMA</span>
                                <span class="bp-login-brand-sub">IT Worklog</span>
                            </span>
                        </span>
                    </a>

                    {{ $slot }}

                    <p class="bp-login-internal-note">
                        Akses internal PT Borneo Prima
                    </p>
                </div>
            </div>

            <div class="bp-login-side hidden lg:block">
                <div class="bp-login-photo"></div>
                <div class="bp-login-veil"></div>

                <div class="bp-login-side-inner">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-200">IT Worklog</p>
                        <h2 class="mt-3 text-2xl font-extrabold leading-tight">
                            Setiap pekerjaan tercatat, setiap progres terlihat.
                        </h2>
                        <p class="mt-3 max-w-md text-sm leading-6 text-slate-300">
                            Catat aktivitas harian, pantau project, dan lihat kontribusi tim IT dalam satu ruang kerja.
                        </p>
                    </div>

                    <div class="bp-login-features">
                        <div class="bp-login-feature">
                            <span class="bp-login-feature-icon">01</span>
                            <div>
                                <strong>Worklog cepat</strong>
                                <small>Catat daftar pekerjaan harian tanpa mengisi durasi.</small>
                            </div>
                        </div>
                        <div class="bp-login-feature">
                            <span class="bp-login-feature-icon">02</span>
                            <div>
                                <strong>Progres terlihat</strong>
                                <small>Pantau project, milestone, dan tugas tim.</small>
                            </div>
                        </div>
                        <div class="bp-login-feature">
                            <span class="bp-login-feature-icon">03</span>
                            <div>
                                <strong>Transparan</strong>
                                <small>Aktivitas tim terdokumentasi rapi dan mudah ditelusuri.</small>
                            </div>
                        </div>
                    </div>

                    <div class="bp-login-side-foot">
                        <span>Kunjungi website perusahaan</span>
                        <a href="https://borneoprima.com/" target="_blank" rel="noopener">borneoprima.com ↗</a>
                    </div>
                </div>
            </div>
        </div>
        @livewireScripts
    </body>
</html>
