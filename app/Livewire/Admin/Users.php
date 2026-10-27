<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Users extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    public string $statusFilter = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public ?string $position = null;

    public string $role = 'member';

    public bool $isActive = true;

    public ?string $password = null;

    public ?string $password_confirmation = null;

    public ?string $successMessage = null;

    public ?string $temporaryPassword = null;

    public ?string $temporaryPasswordEmail = null;

    public function mount(): void
    {
        Gate::authorize('viewAny', User::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        Gate::authorize('create', User::class);

        $this->resetForm();
        $this->showForm = true;
        $this->successMessage = null;
    }

    public function edit(int $userId): void
    {
        $user = User::query()->findOrFail($userId);
        Gate::authorize('update', $user);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->position = $user->position;
        $this->role = $user->role->value;
        $this->isActive = $user->is_active;
        $this->password = null;
        $this->password_confirmation = null;
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
        $user = $this->editingId
            ? User::query()->findOrFail($this->editingId)
            : new User;

        Gate::authorize($this->editingId ? 'update' : 'create', $user);

        $passwordRules = $this->editingId
            ? ['nullable', 'string', 'min:8', 'confirmed']
            : ['required', 'string', 'min:8', 'confirmed'];

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'position' => ['nullable', 'string', 'max:100'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'isActive' => ['boolean'],
            'password' => $passwordRules,
        ]);

        $this->guardOwnAccount($user);
        $this->guardLastAdmin($user, $this->isActive, $this->role);

        $user->fill([
            'name' => $this->name,
            'email' => $this->email,
            'position' => $this->position ?: null,
            'role' => $this->role,
            'is_active' => $this->isActive,
        ]);

        if (! $this->editingId) {
            $user->email_verified_at = now();
        }

        if ($this->password) {
            $user->password = Hash::make($this->password);
        }

        $user->save();

        $this->successMessage = $this->editingId
            ? 'Data pengguna berhasil diperbarui.'
            : 'Pengguna baru berhasil dibuat.';
        $this->showForm = false;
        $this->resetForm();
    }

    public function toggleActive(int $userId): void
    {
        $user = User::query()->findOrFail($userId);
        Gate::authorize('update', $user);

        $next = ! $user->is_active;

        $this->guardOwnAccount($user, $next);
        $this->guardLastAdmin($user, $next, $user->role->value);

        $user->update(['is_active' => $next]);

        $this->successMessage = $next
            ? 'Pengguna berhasil diaktifkan.'
            : 'Pengguna berhasil dinonaktifkan.';
    }

    public function resetPassword(int $userId): void
    {
        $user = User::query()->findOrFail($userId);
        Gate::authorize('update', $user);

        $password = Str::password(12, symbols: false);

        $user->forceFill(['password' => Hash::make($password)])->save();

        $this->temporaryPassword = $password;
        $this->temporaryPasswordEmail = $user->email;
        $this->successMessage = 'Password sementara dibuat. Salin dan bagikan secara pribadi.';
    }

    public function dismissTemporaryPassword(): void
    {
        $this->temporaryPassword = null;
        $this->temporaryPasswordEmail = null;
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, fn ($query) => $query->where(function ($query): void {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('position', 'like', '%'.$this->search.'%');
            }))
            ->when($this->roleFilter, fn ($query) => $query->where('role', $this->roleFilter))
            ->when($this->statusFilter === 'active', fn ($query) => $query->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderByRaw("CASE role WHEN 'admin' THEN 1 WHEN 'manager' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.admin.users', [
            'users' => $users,
            'roles' => UserRole::cases(),
        ]);
    }

    private function guardOwnAccount(User $user, ?bool $nextActive = null): void
    {
        if ($user->id !== auth()->id()) {
            return;
        }

        if ($nextActive === false || ($this->editingId && ! $this->isActive)) {
            throw ValidationException::withMessages([
                'isActive' => 'Anda tidak dapat menonaktifkan akun sendiri.',
            ]);
        }

        if ($this->editingId && $this->role !== UserRole::Admin->value) {
            throw ValidationException::withMessages([
                'role' => 'Anda tidak dapat menurunkan role akun sendiri.',
            ]);
        }
    }

    private function guardLastAdmin(User $user, bool $nextActive, string $nextRole): void
    {
        if (! $user->isAdmin() || ! $user->is_active) {
            return;
        }

        if ($nextActive && $nextRole === UserRole::Admin->value) {
            return;
        }

        $otherAdminExists = User::query()
            ->whereKeyNot($user->id)
            ->where('role', UserRole::Admin->value)
            ->where('is_active', true)
            ->exists();

        if (! $otherAdminExists) {
            throw ValidationException::withMessages([
                'role' => 'Minimal satu admin aktif harus tetap tersedia.',
            ]);
        }
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'name',
            'email',
            'position',
            'password',
            'password_confirmation',
        ]);
        $this->role = UserRole::Member->value;
        $this->isActive = true;
        $this->resetValidation();
    }
}
