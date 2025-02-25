<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    /**
     * Determine whether the user can create a quiz.
     */
    public function create(User $user)
    {
        return $user->hasRole('teacher');
    }

    /**
     * Determine whether the user can update the quiz.
     */
    public function update(User $user, Quiz $quiz)
    {
        return $user->id === $quiz->teacher_id;
    }

    /**
     * Determine whether the user can view the quiz.
     */
    public function view(User $user, Quiz $quiz)
    {
        if ($user->hasRole('teacher')) {
            return $user->id === $quiz->teacher_id;
        }

        if ($user->hasRole('student')) {
            return $quiz->classes()->whereHas('students', function ($query) use ($user) {
                $query->where('student_id', $user->id);
            })->exists();
        }

        return true; // Admin
    }

    /**
     * Determine whether the user can delete the quiz.
     */
    public function delete(User $user, Quiz $quiz)
    {
        return $user->id === $quiz->teacher_id;
    }
}
