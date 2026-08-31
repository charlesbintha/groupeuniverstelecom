<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('users.update');
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('users.delete');
    }

    public function changeRole(User $user, User $target, string $newRole): bool
    {
        if ($user->id === $target->id) {
            return false;
        }

        if (!$user->can('users.update')) {
            return false;
        }

        if ($newRole === 'Admin' && !$user->hasRole('Admin')) {
            return false;
        }

        return true;
    }
}
