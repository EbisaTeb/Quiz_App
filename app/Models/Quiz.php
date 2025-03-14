<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'teacher_id',
        'subject_id',
        'start_time',
        'end_time',
        'time_limit',
        'is_published',
        'published_at',
        'published_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'published_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classes()
    {
        return $this->belongsToMany(ClassGroup::class, 'quiz_class', 'quiz_id', 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
    public function publishedBy()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

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

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function scopeForStudent($query, User $student)
    {
        return $query->whereHas('teacherSubjectClass', function ($q) use ($student) {
            $q->whereIn('class_id', $student->enrolledClasses()->pluck('id'))
                ->whereIn('subject_id', $student->enrolledSubjects()->pluck('id'));
        });
    }

    public function scopeForTeacher($query, User $teacher)
    {
        return $query->whereHas('teacherSubjectClass', function ($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        });
    }
}
