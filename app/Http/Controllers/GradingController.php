<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradingController extends Controller
{
    public function gradeSubmission(Request $request, Submission $submission)
    {
        $this->authorize('grade', $submission);

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.id' => 'required|exists:answers,id',
            'answers.*.score' => 'required|integer|min:0'
        ]);

        foreach ($validated['answers'] as $answer) {
            Answer::find($answer['id'])->update([
                'score' => $answer['score']
            ]);
        }

        $submission->update(['status' => 'graded']);

        return response()->json($submission->fresh()->load('answers'));
    }
}
