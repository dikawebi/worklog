<section class="ui-card p-5 sm:p-6">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Diskusi</p>
            <h2 class="mt-1 text-lg font-bold text-slate-900">Komentar</h2>
        </div>
        <span class="ui-badge bg-slate-100 text-slate-600">{{ $comments->count() }}</span>
    </div>

    @if ($successMessage)
        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">{{ $successMessage }}</div>
    @endif

    <div class="mt-5 space-y-3">
        @forelse ($comments as $comment)
            <article class="rounded-xl border border-slate-200 p-4" wire:key="comment-{{ $comment->id }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold text-slate-900">{{ $comment->user?->name ?? 'Pengguna' }}</p>
                        <p class="text-xs text-slate-400">{{ $comment->created_at?->translatedFormat('d M Y H:i') }}</p>
                    </div>
                    @if ($comment->user_id === auth()->id() || auth()->user()->isAdmin())
                        <button type="button" wire:click="deleteComment({{ $comment->id }})" wire:confirm="Hapus komentar ini?" class="text-xs font-bold text-rose-600 hover:text-rose-700">Hapus</button>
                    @endif
                </div>
                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $comment->body }}</p>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                Belum ada komentar. Mulai diskusi dengan menyebut anggota menggunakan @handle.
            </div>
        @endforelse
    </div>

    <form wire:submit="addComment" class="mt-5 space-y-3">
        <div>
            <label for="comment-body" class="worklog-label">Tulis komentar</label>
            <textarea id="comment-body" wire:model="body" rows="3" maxlength="2000" class="worklog-input" placeholder="Tulis konteks, pertanyaan, atau keputusan..."></textarea>
            @error('body') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
        </div>

        @if ($mentionUsers->isNotEmpty())
            <div class="flex flex-wrap items-center gap-1.5 text-xs text-slate-500">
                <span>Sebut:</span>
                @foreach ($mentionUsers as $mentionUser)
                    <span class="rounded-md bg-slate-100 px-2 py-1 font-mono text-[11px] text-slate-600" title="{{ $mentionUser['name'] }}">@{{ $mentionUser['handle'] }}</span>
                @endforeach
            </div>
        @endif

        <div class="flex justify-end">
            <button type="submit" class="ui-button-primary" wire:loading.attr="disabled">Kirim Komentar</button>
        </div>
    </form>
</section>
