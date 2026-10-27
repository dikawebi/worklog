<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Administrasi / Users</p>
                <h1 class="mt-1 text-xl font-bold text-slate-900">Pengguna</h1>
                <p class="mt-1 text-sm text-slate-500">Kelola akun, role, jabatan, dan status aktif anggota tim.</p>
            </div>

            <a href="{{ route('dashboard') }}" class="ui-button-secondary">Kembali ke Dasbor</a>
        </div>
    </x-slot>

    <livewire:admin.users />
</x-app-layout>
