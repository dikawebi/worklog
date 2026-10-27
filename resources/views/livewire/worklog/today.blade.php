<div class="mx-auto max-w-5xl" wire:loading.class="opacity-75">
    @if ($successMessage)
        <div class="mb-5 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white" aria-hidden="true">✓</span>
            {{ $successMessage }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="ui-card flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand">Laporan harian</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">Pilih tanggal pekerjaan yang ingin dicatat.</p>
            </div>
            <div class="flex flex-col gap-1">
                <label for="worklog-date" class="text-xs font-bold text-slate-600">Tanggal</label>
                <input id="worklog-date" type="date" wire:model.live="date" max="{{ today()->format('Y-m-d') }}" class="worklog-input w-full sm:w-auto" aria-describedby="date-error">
                @error('date') <p id="date-error" class="text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Apa saja yang Anda kerjakan?</h2>
                <p class="mt-1 text-sm text-slate-500">Minimal satu pekerjaan dengan deskripsi wajib.</p>
            </div>
            <span class="ui-badge bg-brand-soft text-brand">{{ count($entries) }} pekerjaan</span>
        </div>

        <div class="space-y-4">
            @foreach ($entries as $index => $entry)
                <article wire:key="worklog-entry-{{ $index }}" class="ui-card worklog-entry-card p-4 sm:p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-soft text-sm font-extrabold text-brand">{{ $index + 1 }}</span>
                            <h3 class="text-sm font-bold text-slate-900">Pekerjaan {{ $index + 1 }}</h3>
                        </div>
                        <button type="button" wire:click="removeEntry({{ $index }})" class="text-xs font-bold text-rose-600 hover:text-rose-700" aria-label="Hapus pekerjaan {{ $index + 1 }}">Hapus</button>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label for="entry-project-{{ $index }}" class="worklog-label">Project <span class="font-normal text-slate-400">(opsional)</span></label>
                            <select id="entry-project-{{ $index }}" wire:model="entries.{{ $index }}.project_id" class="worklog-input">
                                <option value="">Pilih project</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->code }} — {{ $project->name }}</option>
                                @endforeach
                            </select>
                            @error("entries.{$index}.project_id") <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="entry-task-{{ $index }}" class="worklog-label">Task <span class="font-normal text-slate-400">(opsional)</span></label>
                            <select id="entry-task-{{ $index }}" wire:model="entries.{{ $index }}.task_id" class="worklog-input">
                                <option value="">Pilih task</option>
                                @foreach ($tasks as $task)
                                    <option value="{{ $task->id }}">{{ $task->title }}{{ $task->project ? ' — '.$task->project->code : '' }}</option>
                                @endforeach
                            </select>
                            @error("entries.{$index}.task_id") <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="entry-category-{{ $index }}" class="worklog-label">Kategori <span class="font-normal text-slate-400">(opsional)</span></label>
                            <select id="entry-category-{{ $index }}" wire:model="entries.{{ $index }}.category_id" class="worklog-input">
                                <option value="">Pilih kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error("entries.{$index}.category_id") <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="entry-description-{{ $index }}" class="worklog-label">Deskripsi pekerjaan <span class="text-rose-500">*</span></label>
                        <textarea id="entry-description-{{ $index }}" wire:model="entries.{{ $index }}.description" rows="3" maxlength="5000" class="worklog-input" placeholder="Contoh: Menyelesaikan konfigurasi backup server staging." aria-describedby="description-error-{{ $index }}"></textarea>
                        @error("entries.{$index}.description") <p id="description-error-{{ $index }}" class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-4">
                        <label for="entry-attachments-{{ $index }}" class="worklog-label">Lampiran / foto <span class="font-normal text-slate-400">(maks. 3 file, 5 MB/file)</span></label>
                        <input id="entry-attachments-{{ $index }}" type="file" wire:model="attachments.{{ $index }}" multiple accept=".jpg,.jpeg,.png,.webp,.pdf" class="worklog-input file:mr-3 file:rounded-lg file:border-0 file:bg-brand-soft file:px-3 file:py-2 file:text-xs file:font-bold file:text-brand">
                        <p class="mt-1 text-xs text-slate-500">Format: JPG, PNG, WEBP, atau PDF.</p>
                        @error("attachments.{$index}") <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        @error("attachments.{$index}.*") <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror

                        @if (!empty($existingAttachments[$index]))
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($existingAttachments[$index] as $attachment)
                                    <span class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-50 py-1 ps-3 pe-1 text-xs font-semibold text-slate-600">
                                        <a href="{{ $attachment['url'] }}" class="inline-flex items-center gap-2 hover:text-brand">
                                            <span aria-hidden="true">📎</span>
                                            <span class="max-w-[14rem] truncate">{{ $attachment['name'] }}</span>
                                        </a>
                                        <button type="button" wire:click="removeAttachment({{ $index }}, {{ $attachment['id'] }})" wire:confirm="Hapus lampiran ini?" class="rounded-md px-2 py-1 text-rose-600 hover:bg-rose-50" aria-label="Hapus lampiran {{ $attachment['name'] }}">Hapus</button>
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @if (!empty($attachments[$index]))
                            <div class="mt-3 flex flex-wrap gap-2" wire:loading.remove wire:target="attachments.{{ $index }}">
                                @foreach ($attachments[$index] as $upload)
                                    <span class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-2 py-2 text-xs font-semibold text-blue-700">
                                        @if (str_starts_with((string) $upload->getMimeType(), 'image/'))
                                            <img src="{{ $upload->temporaryUrl() }}" alt="Pratinjau {{ $upload->getClientOriginalName() }}" class="h-10 w-10 rounded-md object-cover">
                                        @else
                                            <span class="flex h-10 w-10 items-center justify-center rounded-md bg-blue-100 text-lg" aria-hidden="true">📄</span>
                                        @endif
                                        <span class="max-w-[14rem] truncate">{{ $upload->getClientOriginalName() }}</span>
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        <p wire:loading wire:target="attachments.{{ $index }}" class="mt-2 text-xs font-semibold text-brand">Mengunggah lampiran...</p>
                    </div>
                </article>
            @endforeach
        </div>

        <button type="button" wire:click="addEntry" class="flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-brand/40 bg-brand-soft/40 px-4 py-3 text-sm font-bold text-brand transition hover:border-brand hover:bg-brand-soft">
            <span class="text-lg leading-none">+</span> Tambah pekerjaan
        </button>

        <div class="ui-card p-4 sm:p-5">
            <label for="worklog-notes" class="worklog-label">Catatan / blocker <span class="font-normal text-slate-400">(opsional)</span></label>
            <textarea id="worklog-notes" wire:model="notes" rows="3" maxlength="5000" class="worklog-input" placeholder="Contoh: Menunggu akses VPN dari vendor."></textarea>
            @error('notes') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs font-semibold text-slate-500">Tidak ada durasi atau total jam yang perlu diisi.</p>
            <button type="submit" class="ui-button-primary w-full sm:w-auto" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Simpan Worklog</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
