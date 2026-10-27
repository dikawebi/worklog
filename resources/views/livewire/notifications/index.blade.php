<div class="mx-auto max-w-4xl space-y-5">
    @if ($successMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
            {{ $successMessage }}
        </div>
    @endif

    <section class="ui-card flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
        <div class="inline-flex flex-wrap gap-1 rounded-xl border border-slate-200 bg-white p-1">
            <button type="button" wire:click="setFilter('all')" class="rounded-lg px-3 py-2 text-xs font-bold {{ $filter === 'all' ? 'bg-brand text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                Semua <span class="ml-1 opacity-75">{{ $totalCount }}</span>
            </button>
            <button type="button" wire:click="setFilter('unread')" class="rounded-lg px-3 py-2 text-xs font-bold {{ $filter === 'unread' ? 'bg-brand text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                Belum dibaca <span class="ml-1 opacity-75">{{ $unreadCount }}</span>
            </button>
        </div>

        @if ($unreadCount > 0)
            <button type="button" wire:click="markAllAsRead" class="ui-button-secondary">Tandai semua dibaca</button>
        @endif
    </section>

    <section class="ui-card overflow-hidden">
        <div class="divide-y divide-slate-100">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = $notification->read_at === null;
                @endphp

                <article class="flex items-start gap-3 px-4 py-4 sm:px-5" wire:key="notification-{{ $notification->id }}">
                    <span class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $isUnread ? 'bg-brand-soft text-brand' : 'bg-slate-100 text-slate-500' }}" aria-hidden="true">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0" />
                        </svg>
                    </span>

                    <div class="min-w-0 flex-1">
                        <button type="button" wire:click="open('{{ $notification->id }}')" class="block w-full text-left">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-bold {{ $isUnread ? 'text-slate-900' : 'text-slate-600' }}">{{ $data['title'] ?? 'Notifikasi' }}</p>
                                @if ($isUnread)
                                    <span class="ui-badge bg-brand-soft text-brand">Baru</span>
                                @endif
                            </div>
                            @if (! empty($data['message']))
                                <p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $data['message'] }}</p>
                            @endif
                            <p class="mt-1 text-xs text-slate-400">{{ $notification->created_at?->translatedFormat('d M Y H:i') }}</p>
                        </button>
                    </div>

                    <button type="button" wire:click="deleteNotification('{{ $notification->id }}')" wire:confirm="Hapus notifikasi ini?" class="shrink-0 text-xs font-bold text-slate-400 hover:text-rose-600">Hapus</button>
                </article>
            @empty
                <div class="px-5 py-14 text-center">
                    <p class="text-sm font-bold text-slate-900">{{ $filter === 'unread' ? 'Tidak ada notifikasi baru.' : 'Belum ada notifikasi.' }}</p>
                    <p class="mt-1 text-sm text-slate-500">Penugasan task dan mention komentar akan muncul di sini.</p>
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">{{ $notifications->links() }}</div>
        @endif
    </section>
</div>
