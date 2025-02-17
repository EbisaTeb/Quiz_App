<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'title',
        'description',
        'created_by',
        'start_time',
        'end_time',
        'duration',
        'is_published'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_published' => 'boolean'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'quiz_student', 'quiz_id', 'student_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    // recent add
    // public function class()
    // {
    //     return $this->belongsTo(ClassGroup::class);
    // }

    // public function subject()
    // {
    //     return $this->belongsTo(Subject::class);
    // }
}
