<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\Submission;
use App\Models\User;

class QuizPolicy
{
    /**
     * Create a new policy instance.
     */
    public function grade(User $user, Submission $submission)
    {
        return $submission->quiz->created_by === $user->id;
    }
    public function update(User $user, Quiz $quiz)
    {
        return $user->id === $quiz->created_by;
    }

    public function delete(User $user, $quiz)
    {
        return $user->id === $quiz->created_by;
    }
    public function create(User $user)
    {
        return $user->subjects()->exists() &&
            $user->classes()->exists();
    }

    // public function create(User $user)
    // {
    //     return $user->hasRole('teacher') && 
    //            $user->taughtSubjects()->exists();
    // }

    // public function update(User $user, Quiz $quiz)
    // {
    //     return $user->id === $quiz->created_by;
    // }
}
