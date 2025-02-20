<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $casts = [
        'options' => 'array'
    ];

    protected $fillable = [
        'quiz_id',
        'type',
        'content',
        'options',
        'correct_answer',
        'marks',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
