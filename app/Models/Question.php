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
        'correct_answer',
        'marks',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function matchingPairs()
    {
        return $this->hasMany(MatchingPair::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
