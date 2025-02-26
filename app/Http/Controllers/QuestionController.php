<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class QuestionController extends Controller
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

    public function addQuestions(Request $request)
    {
        try {
            $validated = $request->validate([
                'quiz_id' => ['required', Rule::exists('quizzes', 'id')->where('teacher_id', Auth::id())],
                'questions' => 'required|array|min:1',
                'questions.*.type' => ['required', Rule::in(['mcq', 'short_answer', 'matching'])],
                'questions.*.content' => 'required|string|max:500',
                'questions.*.options' => 'nullable|array', // Only for MCQs
                'questions.*.correct_answer' => ['required_if:questions.*.type,short_answer', 'nullable', 'string'],
                'questions.*.matching_pairs' => ['required_if:questions.*.type,matching', 'nullable', 'array'],
                'questions.*.marks' => 'required|integer|min:1',
            ]);

            $quiz = Quiz::findOrFail($validated['quiz_id']);

            foreach ($validated['questions'] as $question) {
                // check for duplicate questions content in this quiz
                $existingQuestion = $quiz->questions()->where('content', $question['content'])->first();
                if ($existingQuestion) {
                    return response()->json([
                        'message' => "Duplicate question detected",
                        'error' => "A question with the same content already exists in this quiz."
                    ], 409);
                }
                $newQuestion = $quiz->questions()->create([
                    'type' => $question['type'],
                    'content' => $question['content'],
                    'correct_answer' => $question['type'] === 'short_answer' ? $question['correct_answer'] : null,
                    'marks' => $question['marks']
                ]);

                if ($question['type'] === 'mcq' && isset($question['options'])) {
                    foreach ($question['options'] as $option) {
                        $newQuestion->options()->create([
                            'content' => $option['content'],
                            'is_correct' => $option['is_correct'] ?? false,
                        ]);
                    }
                    // Set the correct answer for MCQ
                    $newQuestion->correct_answer = $newQuestion->options()->where('is_correct', true)->pluck('content')->first();
                    $newQuestion->save();
                }

                if ($question['type'] === 'matching' && isset($question['matching_pairs'])) {
                    foreach ($question['matching_pairs'] as $pair) {
                        $newQuestion->matchingPairs()->create([
                            'left_value' => $pair['left_value'],
                            'right_value' => $pair['right_value'],
                        ]);
                    }
                    // Set the correct answer for matching
                    $newQuestion->correct_answer = $newQuestion->matchingPairs()->pluck('right_value', 'left_value');
                    $newQuestion->save();
                }
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

    public function updateQuestion(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'content' => 'required|string|max:500',
                'correct_answer' => 'nullable|string',
                'marks' => 'required|integer|min:1',
                'options' => 'nullable|array',
                'matching_pairs' => 'nullable|array',
            ]);

            $question = Question::findOrFail($id);

            $question->update([
                'content' => $validated['content'],
                'correct_answer' => $validated['correct_answer'],
                'marks' => $validated['marks'],
            ]);

            if ($question->type === 'mcq' && isset($validated['options'])) {
                $question->options()->delete();
                foreach ($validated['options'] as $option) {
                    $question->options()->create([
                        'content' => $option['content'],
                        'is_correct' => $option['is_correct'] ?? false,
                    ]);
                }
                $question->correct_answer = $question->options()->where('is_correct', true)->pluck('content')->first();
                $question->save();
            }

            if ($question->type === 'matching' && isset($validated['matching_pairs'])) {
                $question->matchingPairs()->delete();
                foreach ($validated['matching_pairs'] as $pair) {
                    $question->matchingPairs()->create([
                        'left_value' => $pair['left_value'],
                        'right_value' => $pair['right_value'],
                    ]);
                }
                $question->correct_answer = $question->matchingPairs()->pluck('right_value', 'left_value');
                $question->save();
            }

            return response()->json($question, 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Question not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function deleteQuestion($id)
    {
        try {
            $question = Question::findOrFail($id);
            $question->delete();

            return response()->json(['message' => 'Question deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Question not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function getQuestion($id)
    {
        try {
            $question = Question::with(['options', 'matchingPairs'])->findOrFail($id);
            return response()->json($question, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Question not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function getQuizQuestions($quiz_id)
    {
        try {
            $quiz = Quiz::with('questions.options', 'questions.matchingPairs')->findOrFail($quiz_id);
            return response()->json($quiz->questions, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Quiz not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }
}
