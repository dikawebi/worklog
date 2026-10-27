<div class="mx-auto max-w-5xl space-y-5">
    @if ($successMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
            {{ $successMessage }}
        </div>
    @endif

    @error('category')
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800" role="alert">{{ $message }}</div>
    @enderror

    @if ($showForm)
        <section class="ui-card border-brand/30 p-5 sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">{{ $editingId ? 'Edit kategori' : 'Kategori baru' }}</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Informasi kategori</h2>
                </div>
                <button type="button" wire:click="cancelForm" class="text-sm font-bold text-slate-400 hover:text-slate-700">Tutup</button>
            </div>

            <form wire:submit="save" class="mt-5 space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="category-name" class="worklog-label">Nama kategori <span class="text-rose-500">*</span></label>
                        <input id="category-name" type="text" wire:model="name" maxlength="100" class="worklog-input" @disabled($editingId && $name === 'Umum')>
                        @error('name') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="category-color" class="worklog-label">Warna <span class="text-rose-500">*</span></label>
                        <div class="flex items-center gap-2">
                            <input id="category-color" type="color" wire:model="color" class="h-10 w-14 cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                            <input type="text" wire:model="color" maxlength="7" class="worklog-input font-mono uppercase" aria-label="Kode warna hex">
                        </div>
                        @error('color') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="category-sort" class="worklog-label">Urutan tampil</label>
                        <input id="category-sort" type="number" wire:model="sortOrder" min="0" max="9999" class="worklog-input">
                        @error('sortOrder') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-end pb-2">
                        <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <input type="checkbox" wire:model="isActive" class="rounded border-slate-300 text-brand focus:ring-brand" @disabled($editingId && $name === 'Umum')>
                            Kategori aktif dan tampil di form worklog
                        </label>
                    </div>
                </div>
                @error('isActive') <p class="text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror

                @if ($editingId && $name === 'Umum')
                    <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800">
                        Kategori Umum adalah kategori default dan tidak dapat dinonaktifkan atau diganti namanya.
                    </p>
                @endif

                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="cancelForm" class="ui-button-secondary">Batal</button>
                    <button type="submit" class="ui-button-primary" wire:loading.attr="disabled">
                        {{ $editingId ? 'Simpan Perubahan' : 'Buat Kategori' }}
                    </button>
                </div>
            </form>
        </section>
    @endif

    <section class="ui-card p-4 sm:p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div class="flex-1">
                <label for="category-search" class="sr-only">Cari kategori</label>
                <input id="category-search" type="search" wire:model.live.debounce.300ms="search" class="worklog-input" placeholder="Cari kategori...">
            </div>
            <button type="button" wire:click="create" class="ui-button-primary shrink-0">+ Kategori</button>
        </div>
    </section>

    <section class="ui-card overflow-hidden">
        <div class="table-wrap overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Urutan</th>
                        <th class="px-4 py-3">Dipakai</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($categories as $category)
                        <tr wire:key="category-{{ $category->id }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full ring-2 ring-white" style="background-color: {{ $category->color }}"></span>
                                    <span class="font-bold text-slate-900">{{ $category->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $category->sort_order }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $category->work_log_entries_count }} pekerjaan</td>
                            <td class="px-4 py-3">
                                <span class="ui-badge {{ $category->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap justify-end gap-1.5">
                                    <button type="button" wire:click="edit({{ $category->id }})" class="ui-button-secondary !px-3 !py-1.5 text-xs">Edit</button>
                                    @if ($category->name !== 'Umum')
                                        <button type="button" wire:click="toggleActive({{ $category->id }})" class="ui-button-secondary !px-3 !py-1.5 text-xs">
                                            {{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    @else
                                        <span class="px-3 py-1.5 text-xs font-semibold text-slate-400">Default</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">Tidak ada kategori yang sesuai pencarian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
