<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSubjectClass extends Model
{
    protected $table = 'teacher_subject_class';

    protected $fillable = ['teacher_id', 'subject_id', 'class_id'];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassGroup::class, 'class_id');
    }
}
