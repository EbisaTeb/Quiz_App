<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class QuizStudent extends Pivot
{
    protected $table = 'quiz_student';
}
