<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name'];

    // public function teacherSubjectClasses()
    // {
    //     return $this->hasMany(TeacherSubjectClass::class, 'subject_id');
    // }




    // public function teachers()
    // {
    //     return $this->belongsToMany(User::class, 'teacher_subject_class')
    //         ->withPivot('class_id');
    // }






    // public function classes()
    // {
    //     return $this->belongsToMany(ClassGroup::class, 'teacher_subject_class')
    //         ->withPivot('teacher_id');
    // }

    // public function classes()
    // {
    //     return $this->belongsToMany(ClassGroup::class, 'student_subject', 'subject_id', 'class_id');
    // }


    public function teacherSubjectClasses()
    {
        return $this->hasMany(TeacherSubjectClass::class, 'subject_id');
    }


    public function enrollSubjectsStudent()
    {
        return $this->belongsToMany(User::class, 'student_subject', 'subject_id', 'student_id')
            ->withPivot('class_id')
            ->withTimestamps();
    }
}
