<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    // public function approve(User $user)
    // {
    //     return $user->hasRole('admin');
    // }

    public function manageTeachers(User $user)
    {
        return $user->hasRole('admin');
    }

    public function manageStudents(User $user)
    {
        return $user->hasRole('admin');
    }
    public function viewAny(User $user)
    {
        return $user->hasRole('admin'); // Ensure only admins can fetch all users
    }
    public function managementClass(User $user)
    {
        return $user->hasRole('admin');
    }
    public function managementSubject(User $user)
    {
        return $user->hasRole('admin');
    }
}
