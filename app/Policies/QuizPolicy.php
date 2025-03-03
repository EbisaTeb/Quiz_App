<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\StudentSubject;
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
            // Check student enrollment through StudentSubject and quiz availability
            return $quiz->is_published && now()->isBetween($quiz->start_time, $quiz->end_time) && StudentSubject::where('student_id', $user->id)
                ->where('subject_id', $quiz->subject_id)
                ->whereIn('class_id', $quiz->classes->pluck('id'))
                ->exists();
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
