<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = [
        'submission_id',
        'question_id',
        'option_id',
        'answer_text',
        'matched_pairs',
        'score'
    ];

    protected $casts = [
        'matched_pairs' => 'json'
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function option()
    {
        return $this->belongsTo(Option::class);
    }
}
