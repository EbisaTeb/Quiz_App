<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ShortAnswerScoring extends Controller
{
    public function getTeacherQuizzes(Request $request)
    {
        // dd($request->all());
        try {
            $quizzes = Quiz::where('teacher_id', Auth::id())->get();

            if ($quizzes->isEmpty()) {
                return response()->json(['message' => 'No quizzes found'], 404);
            }

            return response()->json($quizzes);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function getSubmissionShortAnswer($quizId)
    {
        try {
            $submissions = QuizAttempt::where('quiz_id', $quizId)
                ->with(['student', 'answers' => function ($query) {
                    $query->whereHas('question', function ($q) {
                        $q->where('type', 'short_answer');
                    })->with('question');
                }])
                ->get();

            if ($submissions->isEmpty()) {
                return response()->json(['message' => 'No submissions found'], 404);
            }

            $result = $submissions->map(function ($submission) {
                return [
                    'id' => $submission->id,
                    'student_name' => $submission->student->name,
                    'answers' => $submission->answers->map(function ($answer) {
                        return [
                            'answer_id' => $answer->id,
                            'question_id' => $answer->question_id,
                            'question' => $answer->question->content,
                            'student_answer' => $answer->student_answer,
                            'correct_answer' => $answer->question->correct_answer,
                            'marks_obtained' => $answer->marks_obtained,
                            'max_marks' => $answer->question->marks,
                        ];
                    }),
                ];
            });

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateShortAnswerScore(Request $request, $submissionId, $questionId)
    {
        try {
            $submission = QuizAttempt::findOrFail($submissionId);
            $answer = $submission->answers()->where('question_id', $questionId)->firstOrFail();
            $maxMarks = $answer->question->marks;

            $validated = $request->validate([
                'score' => 'required|numeric|min:0|max:' . $maxMarks,
            ]);

            $answer->update([
                'marks_obtained' => $validated['score'],
                'is_correct' => $validated['score'] > 0,
            ]);

            // Update total score
            $totalScore = $submission->answers->sum('marks_obtained');
            $submission->update(['score' => $totalScore]);

            return response()->json(['message' => 'Score updated successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Error updating score: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }
}
