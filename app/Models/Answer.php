<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $table = 'answers';
    protected $fillable = ['attempt_id', 'question_id', 'student_answer', 'is_correct', 'marks_obtained', 'is_published'];
    protected $casts = [
        'student_answer' => 'array',
        'is_published' => 'boolean',
    ];

    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
