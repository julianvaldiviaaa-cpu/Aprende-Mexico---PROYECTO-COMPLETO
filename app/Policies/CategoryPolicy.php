<?php

namespace App\Policies;

use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->status === 'active' && $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->status === 'active' && $user->isAdmin();
    }
}
