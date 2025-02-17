<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassGroup extends Model
{
    // protected $table = 'class_groups';
    protected $table = 'classes';
    protected $fillable = ['name', 'grade_level', 'year'];

    public function students()
    {
        return $this->belongsToMany(User::class, 'student_subject', 'class_id', 'student_id');
    }


    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject_class', 'class_id', 'teacher_id')
            ->withPivot('teacher_id');
    }


    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_subject_class', 'class_id', 'subject_id')
            ->distinct();
    }

    public function teacherSubjectClasses()
    {
        return $this->hasMany(TeacherSubjectClass::class, 'class_id');
    }
}
