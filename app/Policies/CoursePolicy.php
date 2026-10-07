<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->status === 'active';
    }

    public function create(User $user): bool
    {
        return $user->canCreateCourses();
    }

    public function update(User $user, Course $course): bool
    {
        return $user->canCreateCourses() && ($user->isAdmin() || $course->instructor_id === $user->id);
    }
}
