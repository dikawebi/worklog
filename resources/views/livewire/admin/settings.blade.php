<div class="mx-auto max-w-4xl space-y-5">
    @if ($successMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
            {{ $successMessage }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-5">
        <section class="ui-card p-5 sm:p-6">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Identitas aplikasi</p>
                <h2 class="mt-1 text-lg font-bold text-slate-900">Pengaturan umum</h2>
                <p class="mt-1 text-sm text-slate-500">Nama aplikasi dan zona waktu yang dipakai seluruh tampilan.</p>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <label for="settings-app-name" class="worklog-label">Nama aplikasi <span class="text-rose-500">*</span></label>
                    <input id="settings-app-name" type="text" wire:model="appName" maxlength="100" class="worklog-input">
                    @error('appName') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="settings-timezone" class="worklog-label">Zona waktu <span class="text-rose-500">*</span></label>
                    <select id="settings-timezone" wire:model="timezone" class="worklog-input">
                        @foreach ($timezones as $zone)
                            <option value="{{ $zone }}">{{ $zone }}</option>
                        @endforeach
                    </select>
                    @error('timezone') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="ui-card p-5 sm:p-6">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Jadwal kerja</p>
                <h2 class="mt-1 text-lg font-bold text-slate-900">Hari kerja dan pengingat</h2>
                <p class="mt-1 text-sm text-slate-500">Pengingat email hanya dikirim pada hari kerja yang dipilih dan setelah pukul yang ditentukan.</p>
            </div>

            <div class="mt-5 space-y-4">
                <div>
                    <p class="worklog-label">Hari kerja <span class="text-rose-500">*</span></p>
                    <div class="mt-2 flex flex-wrap gap-3">
                        @foreach ($dayLabels as $value => $label)
                            <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700">
                                <input type="checkbox" wire:model="workdays" value="{{ $value }}" class="rounded border-slate-300 text-brand focus:ring-brand">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('workdays') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    @error('workdays.*') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="max-w-xs">
                    <label for="settings-reminder-time" class="worklog-label">Jam pengingat <span class="text-rose-500">*</span></label>
                    <input id="settings-reminder-time" type="time" wire:model="reminderTime" class="worklog-input">
                    @error('reminderTime') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="ui-card p-5 sm:p-6">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Email</p>
                <h2 class="mt-1 text-lg font-bold text-slate-900">Pengirim notifikasi</h2>
                <p class="mt-1 text-sm text-slate-500">Alamat ini dipakai saat fitur pengingat email diaktifkan pada Sprint 5.</p>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <label for="settings-mail-address" class="worklog-label">Alamat pengirim</label>
                    <input id="settings-mail-address" type="email" wire:model="mailFromAddress" maxlength="255" class="worklog-input" placeholder="admin.it@borneoprima.com">
                    @error('mailFromAddress') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="settings-mail-name" class="worklog-label">Nama pengirim</label>
                    <input id="settings-mail-name" type="text" wire:model="mailFromName" maxlength="100" class="worklog-input" placeholder="IT Worklog">
                    @error('mailFromName') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <div class="flex justify-end gap-2">
            <a href="{{ route('dashboard') }}" class="ui-button-secondary">Batal</a>
            <button type="submit" class="ui-button-primary" wire:loading.attr="disabled">Simpan Pengaturan</button>
        </div>
    </form>
</div>
