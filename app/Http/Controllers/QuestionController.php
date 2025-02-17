<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    public function store(Request $request, Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        $validated = $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|in:mcq,short_answer,matching',
            'points' => 'required|integer|min:1',
            'options' => 'required_if:type,mcq|array',
            'matching_pairs' => 'required_if:type,matching|json',
            'keywords' => 'nullable|string'
        ]);

        $question = $quiz->questions()->create([
            'question_text' => $validated['question_text'],
            'type' => $validated['type'],
            'points' => $validated['points'],
            'matching_pairs' => $validated['matching_pairs'] ?? null,
            'keywords' => $validated['keywords'] ?? null
        ]);

        if ($validated['type'] === 'mcq') {
            foreach ($validated['options'] as $option) {
                $question->options()->create([
                    'option_text' => $option['text'],
                    'is_correct' => $option['is_correct']
                ]);
            }
        }

        return response()->json($question->load('options'), 201);
    }
}
