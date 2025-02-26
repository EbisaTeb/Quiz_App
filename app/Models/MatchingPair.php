<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchingPair extends Model
{
    use HasFactory;

    protected $table = 'matching_pairs';
    protected $fillable = [
        'question_id',
        'left_value',
        'right_value',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
