<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->exists;
    }

    public function view(User $user, Task $task): bool
    {
        return $this->hasMembership($user, $task);
    }

    public function create(User $user): bool
    {
        return $user->exists;
    }

    public function update(User $user, Task $task): bool
    {
        $role = $this->membershipRole($user, $task);

        return in_array($role, ['owner', 'admin', 'member'], true);
    }

    public function delete(User $user, Task $task): bool
    {
        $role = $this->membershipRole($user, $task);

        return in_array($role, ['owner', 'admin'], true);
    }

    private function hasMembership(User $user, Task $task): bool
    {
        return $this->membershipRole($user, $task) !== null;
    }

    private function membershipRole(User $user, Task $task): ?string
    {
        return $user->teams()
            ->where('teams.id', $task->project->team_id)
            ->value('team_user.role');
    }
}
