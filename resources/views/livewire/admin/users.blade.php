<div class="mx-auto max-w-7xl space-y-5">
    @if ($successMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
            {{ $successMessage }}
        </div>
    @endif

    @if ($temporaryPassword)
        <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900" role="status">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="font-bold">Password sementara untuk {{ $temporaryPasswordEmail }}</p>
                    <p class="mt-1">Salin sekarang. Password ini tidak ditampilkan lagi setelah panel ditutup.</p>
                    <code class="mt-2 block select-all rounded-lg border border-amber-200 bg-white px-3 py-2 font-mono text-base font-bold tracking-wide text-slate-900">{{ $temporaryPassword }}</code>
                </div>
                <button type="button" wire:click="dismissTemporaryPassword" class="ui-button-secondary shrink-0">Tutup</button>
            </div>
        </div>
    @endif

    @if ($showForm)
        <section class="ui-card border-brand/30 p-5 sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">{{ $editingId ? 'Edit pengguna' : 'Pengguna baru' }}</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Informasi akun</h2>
                </div>
                <button type="button" wire:click="cancelForm" class="text-sm font-bold text-slate-400 hover:text-slate-700" aria-label="Tutup form">Tutup</button>
            </div>

            <form wire:submit="save" class="mt-5 space-y-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="user-name" class="worklog-label">Nama lengkap <span class="text-rose-500">*</span></label>
                        <input id="user-name" type="text" wire:model="name" maxlength="255" class="worklog-input" autocomplete="name">
                        @error('name') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="user-email" class="worklog-label">Email <span class="text-rose-500">*</span></label>
                        <input id="user-email" type="email" wire:model="email" maxlength="255" class="worklog-input" autocomplete="email">
                        @error('email') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="user-position" class="worklog-label">Jabatan</label>
                        <input id="user-position" type="text" wire:model="position" maxlength="100" class="worklog-input" placeholder="IT Support">
                        @error('position') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="user-role" class="worklog-label">Role <span class="text-rose-500">*</span></label>
                        <select id="user-role" wire:model="role" class="worklog-input">
                            @foreach ($roles as $userRole)
                                <option value="{{ $userRole->value }}">{{ $userRole->label() }}</option>
                            @endforeach
                        </select>
                        @error('role') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="user-password" class="worklog-label">
                            {{ $editingId ? 'Password baru (opsional)' : 'Password awal' }}
                            @if (! $editingId) <span class="text-rose-500">*</span> @endif
                        </label>
                        <input id="user-password" type="password" wire:model="password" class="worklog-input" autocomplete="new-password">
                        @error('password') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="user-password-confirmation" class="worklog-label">Ulangi password</label>
                        <input id="user-password-confirmation" type="password" wire:model="password_confirmation" class="worklog-input" autocomplete="new-password">
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                    <input type="checkbox" wire:model="isActive" class="rounded border-slate-300 text-brand focus:ring-brand">
                    Akun aktif dan dapat login
                </label>
                @error('isActive') <p class="text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror

                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="cancelForm" class="ui-button-secondary">Batal</button>
                    <button type="submit" class="ui-button-primary" wire:loading.attr="disabled">
                        {{ $editingId ? 'Simpan Perubahan' : 'Buat Pengguna' }}
                    </button>
                </div>
            </form>
        </section>
    @endif

    <section class="ui-card p-4 sm:p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="grid flex-1 gap-3 sm:grid-cols-3">
                <div>
                    <label for="user-search" class="sr-only">Cari pengguna</label>
                    <input id="user-search" type="search" wire:model.live.debounce.300ms="search" class="worklog-input" placeholder="Cari nama, email, atau jabatan...">
                </div>
                <div>
                    <label for="user-role-filter" class="sr-only">Filter role</label>
                    <select id="user-role-filter" wire:model.live="roleFilter" class="worklog-input">
                        <option value="">Semua role</option>
                        @foreach ($roles as $userRole)
                            <option value="{{ $userRole->value }}">{{ $userRole->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="user-status-filter" class="sr-only">Filter status</label>
                    <select id="user-status-filter" wire:model.live="statusFilter" class="worklog-input">
                        <option value="">Semua status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
            </div>

            <button type="button" wire:click="create" class="ui-button-primary shrink-0">+ Pengguna</button>
        </div>
    </section>

    <section class="ui-card overflow-hidden">
        <div class="table-wrap overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Pengguna</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Login terakhir</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td class="px-4 py-3">
                                <p class="font-bold text-slate-900">{{ $user->name }}</p>
                                <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                @if ($user->position)
                                    <p class="mt-0.5 text-xs font-semibold text-brand">{{ $user->position }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="ui-badge {{ $user->role->value === 'admin' ? 'bg-blue-100 text-blue-700' : ($user->role->value === 'manager' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $user->role->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="ui-badge {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500">
                                {{ $user->last_login_at?->translatedFormat('d M Y H:i') ?? 'Belum pernah' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap justify-end gap-1.5">
                                    <button type="button" wire:click="edit({{ $user->id }})" class="ui-button-secondary !px-3 !py-1.5 text-xs">Edit</button>
                                    @if ($user->id !== auth()->id())
                                        <button type="button" wire:click="toggleActive({{ $user->id }})" class="ui-button-secondary !px-3 !py-1.5 text-xs">
                                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                        <button type="button" wire:click="resetPassword({{ $user->id }})" wire:confirm="Buat password sementara baru untuk {{ $user->email }}?" class="ui-button-secondary !px-3 !py-1.5 text-xs">
                                            Reset password
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">Tidak ada pengguna yang sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">{{ $users->links() }}</div>
        @endif
    </section>
</div>
