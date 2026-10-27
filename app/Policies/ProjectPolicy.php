<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Project $project): bool
    {
        return $user->isManager()
            || $project->owner_id === $user->id
            || $project->members()->whereKey($user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isManager();
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isManager();
    }
}
