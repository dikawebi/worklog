<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Category;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Categories extends Component
{
    public string $search = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $color = '#0066FF';

    public bool $isActive = true;

    public int $sortOrder = 0;

    public ?string $successMessage = null;

    public function mount(): void
    {
        Gate::authorize('viewAny', Category::class);
    }

    public function create(): void
    {
        Gate::authorize('create', Category::class);

        $this->resetForm();
        $this->showForm = true;
        $this->successMessage = null;
    }

    public function edit(int $categoryId): void
    {
        $category = Category::query()->findOrFail($categoryId);
        Gate::authorize('update', $category);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->color = $category->color;
        $this->isActive = $category->is_active;
        $this->sortOrder = $category->sort_order;
        $this->showForm = true;
        $this->successMessage = null;
        $this->resetValidation();
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function save(): void
    {
        $category = $this->editingId
            ? Category::query()->findOrFail($this->editingId)
            : new Category;

        Gate::authorize($this->editingId ? 'update' : 'create', $category);

        $this->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'isActive' => ['boolean'],
            'sortOrder' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);

        $isUmum = $category->exists && $category->name === 'Umum';

        $category->fill([
            'name' => $isUmum ? 'Umum' : $this->name,
            'color' => strtoupper($this->color),
            'is_active' => $isUmum ? true : $this->isActive,
            'sort_order' => $this->sortOrder,
        ])->save();

        $this->successMessage = $this->editingId
            ? 'Kategori berhasil diperbarui.'
            : 'Kategori baru berhasil dibuat.';
        $this->showForm = false;
        $this->resetForm();
    }

    public function toggleActive(int $categoryId): void
    {
        $category = Category::query()->findOrFail($categoryId);
        Gate::authorize('update', $category);

        if ($category->name === 'Umum' && $category->is_active) {
            $this->addError('category', 'Kategori Umum harus tetap aktif.');

            return;
        }

        $category->update(['is_active' => ! $category->is_active]);
        $this->successMessage = $category->is_active
            ? 'Kategori berhasil diaktifkan.'
            : 'Kategori berhasil dinonaktifkan.';
    }

    public function render()
    {
        $categories = Category::query()
            ->when($this->search, fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->withCount('workLogEntries')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.categories', [
            'categories' => $categories,
        ]);
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'color', 'sortOrder']);
        $this->color = '#0066FF';
        $this->isActive = true;
        $this->sortOrder = 0;
        $this->resetValidation();
    }
}
