<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    public function view(User $user, Submission $submission)
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'teacher') {
            return $submission->quiz->created_by === $user->id;
        }


        return $user->id === $submission->student_id ||
            $submission->quiz->created_by === $user->id;
    }
}
