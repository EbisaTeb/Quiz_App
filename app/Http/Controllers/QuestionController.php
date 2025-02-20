<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class QuestionController extends Controller
{
    public function getTeacherQuizzes()
    {
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

    public function addQuestions(Request $request)
    {
        try {
            $validated = $request->validate([
                'quiz_id' => ['required', Rule::exists('quizzes', 'id')->where('teacher_id', Auth::id())],
                'questions' => 'required|array|min:1',
                'questions.*.type' => ['required', Rule::in(['mcq', 'short_answer', 'matching'])],
                'questions.*.content' => 'required|string|max:500',
                'questions.*.options' => 'nullable|array', // Only for MCQs
                'questions.*.correct_answer' => ['required', function ($attribute, $value, $fail) {
                    if (!is_string($value) && !is_array($value)) {
                        $fail("The $attribute field must be a string or an array.");
                    }
                }],
                'questions.*.marks' => 'required|integer|min:1',
            ]);

            $quiz = Quiz::findOrFail($validated['quiz_id']);

            foreach ($validated['questions'] as $question) {
                $quiz->questions()->create([
                    'type' => $question['type'],
                    'content' => $question['content'],
                    'options' => isset($question['options']) ? json_encode($question['options']) : null, // ✅ Ensure key exists
                    'correct_answer' => isset($question['correct_answer'])
                        ? (is_array($question['correct_answer']) ? json_encode($question['correct_answer']) : $question['correct_answer'])
                        : null, // ✅ Ensure key exists
                    'marks' => $question['marks']
                ]);
            }

            return response()->json($quiz->load('questions'), 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Quiz not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }
}
