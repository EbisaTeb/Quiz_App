<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'teacher_id',
        'class_id',
        'subject_id',
        'start_time',
        'end_time',
        'time_limit',
        'is_published',
        'published_at',
        'published_by',
    ];

    /**
     * Get the teacher that owns the quiz.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassGroup::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function PublishedBy()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * Get the teacherSubjectClass relationship.
     */
    public function teacherSubjectClass()
    {
        return $this->belongsTo(TeacherSubjectClass::class, 'class_id', 'class_id')
            ->where('subject_id', $this->subject_id)
            ->where('teacher_id', $this->teacher_id);
    }
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    // public function attempts()
    // {
    //     return $this->hasMany(QuizAttempt::class);
    // }





    // Get quizzes available to student based on StudentSubject
    public function scopeForStudent($query, User $student)
    {
        return $query->whereHas('teacherSubjectClass', function ($q) use ($student) {
            $q->whereIn('class_id', $student->enrolledClasses()->pluck('id'))
                ->whereIn('subject_id', $student->enrolledSubjects()->pluck('id'));
        });
    }

    // Get quizzes created by teacher through TeacherSubjectClass
    public function scopeForTeacher($query, User $teacher)
    {
        return $query->whereHas('teacherSubjectClass', function ($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        });
    }
}
