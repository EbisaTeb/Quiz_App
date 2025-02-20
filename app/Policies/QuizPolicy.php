<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\StudentSubject;
use App\Models\User;

class QuizPolicy
{
    /**
     * Create a new policy instance.
     */
    public function create(User $user)
    {
        return $user->hasRole('teacher');
    }

    public function update(User $user, Quiz $quiz)
    {
        $teacherSubjectClass = $quiz->teacherSubjectClass;
        return $teacherSubjectClass && $user->id === $teacherSubjectClass->teacher_id;
    }

    public function view(User $user, Quiz $quiz)
    {
        $teacherSubjectClass = $quiz->teacherSubjectClass;

        if ($user->hasRole('teacher')) {
            return $teacherSubjectClass && $user->id === $teacherSubjectClass->teacher_id;
        }

        if ($user->hasRole('student')) {
            return $teacherSubjectClass && StudentSubject::where('student_id', $user->id)
                ->where('class_id', $teacherSubjectClass->class_id)
                ->where('subject_id', $teacherSubjectClass->subject_id)
                ->exists();
        }

        return true; // Admin
    }

    public function delete(User $user, Quiz $quiz)
    {
        $teacherSubjectClass = $quiz->teacherSubjectClass;
        return $teacherSubjectClass && $user->id === $teacherSubjectClass->teacher_id;
    }
}
