<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class StudentSubject extends Pivot
{
    protected $table = 'student_subject';
    protected $fillable = ['student_id', 'subject_id', 'class_id'];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassGroup::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
    public static function isStudentEnrolled($studentId, $subjectId, $classId)
    {
        return self::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('class_id', $classId)
            ->exists();
    }
}
