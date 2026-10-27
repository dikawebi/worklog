<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        return $user->isManager()
            || $task->assignee_id === $user->id
            || $task->project?->owner_id === $user->id
            || $task->project?->members()->whereKey($user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Task $task): bool
    {
        return $user->isManager();
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->isManager();
    }

    public function changeStatus(User $user, Task $task): bool
    {
        return $user->isManager() || $task->assignee_id === $user->id;
    }
}
