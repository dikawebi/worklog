<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Administrasi / Audit Log</p>
                <h1 class="mt-1 text-xl font-bold text-slate-900">Audit Log</h1>
                <p class="mt-1 text-sm text-slate-500">Telusuri perubahan data penting beserta pelaku dan waktunya.</p>
            </div>

            <a href="{{ route('dashboard') }}" class="ui-button-secondary">Kembali ke Dasbor</a>
        </div>
    </x-slot>

    <livewire:admin.audit-log />
</x-app-layout>
