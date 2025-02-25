<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizClass extends Model
{
    //
    protected $table = 'quiz_class';
    protected $fillable = ['quiz_id', 'class_id'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassGroup::class, 'class_id');
    }
}
