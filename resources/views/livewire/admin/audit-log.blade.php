<div class="mx-auto max-w-7xl space-y-5">
    <section class="ui-card p-4 sm:p-5">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
            <div class="xl:col-span-2">
                <label for="audit-search" class="sr-only">Cari audit</label>
                <input id="audit-search" type="search" wire:model.live.debounce.300ms="search" class="worklog-input" placeholder="Cari pelaku, subjek, atau deskripsi...">
            </div>
            <div>
                <label for="audit-event" class="sr-only">Event</label>
                <select id="audit-event" wire:model.live="eventFilter" class="worklog-input">
                    <option value="">Semua event</option>
                    @foreach ($events as $event)
                        <option value="{{ $event }}">{{ ucfirst($event) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="audit-user" class="sr-only">Pelaku</label>
                <select id="audit-user" wire:model.live="userFilter" class="worklog-input">
                    <option value="">Semua pelaku</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label for="audit-from" class="sr-only">Dari tanggal</label>
                    <input id="audit-from" type="date" wire:model.live="from" class="worklog-input">
                </div>
                <div>
                    <label for="audit-to" class="sr-only">Sampai tanggal</label>
                    <input id="audit-to" type="date" wire:model.live="to" class="worklog-input">
                </div>
            </div>
        </div>
        <p class="mt-3 text-xs text-slate-500">Riwayat perubahan data penting. Nilai lama dan nilai baru ditampilkan untuk setiap perubahan.</p>
    </section>

    <section class="ui-card overflow-hidden">
        <div class="table-wrap overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Pelaku</th>
                        <th class="px-4 py-3">Event</th>
                        <th class="px-4 py-3">Subjek</th>
                        <th class="px-4 py-3">Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($activities as $activity)
                        @php
                            $old = (array) ($activity->properties['old'] ?? []);
                            $new = (array) ($activity->properties['attributes'] ?? []);
                            $keys = array_values(array_unique(array_merge(array_keys($old), array_keys($new))));
                            $format = fn ($value) => is_null($value) ? '—' : (is_bool($value) ? ($value ? 'true' : 'false') : (is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value));
                        @endphp
                        <tr wire:key="activity-{{ $activity->id }}">
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500">{{ $activity->created_at?->translatedFormat('d M Y H:i:s') }}</td>
                            <td class="px-4 py-3">
                                <p class="font-bold text-slate-900">{{ $activity->causer?->name ?? 'Sistem' }}</p>
                                <p class="text-xs text-slate-500">{{ $activity->causer?->email ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="ui-badge {{ $activity->event === 'created' ? 'bg-emerald-100 text-emerald-700' : ($activity->event === 'deleted' ? 'bg-rose-100 text-rose-700' : ($activity->event === 'restored' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-800')) }}">
                                    {{ ucfirst($activity->event ?? $activity->description) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-xs font-bold text-slate-700">{{ class_basename($activity->subject_type ?? '—') }}</p>
                                <p class="text-xs text-slate-500">#{{ $activity->subject_id ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if ($keys)
                                    <ul class="space-y-1">
                                        @foreach ($keys as $key)
                                            <li class="text-xs leading-5 text-slate-600">
                                                <span class="font-bold text-slate-700">{{ $key }}</span>:
                                                <span class="text-rose-600">{{ $format($old[$key] ?? null) }}</span>
                                                <span class="text-slate-400">→</span>
                                                <span class="text-emerald-700">{{ $format($new[$key] ?? null) }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-xs text-slate-500">{{ $activity->description }}</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">Belum ada aktivitas yang sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($activities->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">{{ $activities->links() }}</div>
        @endif
    </section>
</div>
