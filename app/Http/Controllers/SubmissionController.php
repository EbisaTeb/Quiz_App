<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\MatchingPair;
use App\Models\StudentSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SubmissionController extends Controller
{
    public function fetchStudentQuizzes()
    {
        try {
            $user = Auth::user();

            if (!$user->hasRole('student')) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $quizzes = Quiz::whereHas('classes.students', function ($query) use ($user) {
                $query->where('student_id', $user->id);
            })
                ->where('is_published', true)
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->with(['classes', 'subject'])
                ->get();

            return response()->json($quizzes);
        } catch (\Exception $e) {
            Log::error('Error fetching student quizzes: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function fetchActiveQuizzes()
    {
        try {
            $user = Auth::user();

            if (!$user->hasRole('student')) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }


            // dd($request->all());

            $quizzes = Quiz::whereHas('classes.students', function ($query) use ($user) {
                $query->where('student_id', $user->id);
            })
                ->where('is_published', true)
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->with(['classes', 'subject'])
                ->get();

            return response()->json($quizzes);
        } catch (\Exception $e) {
            Log::error('Error fetching active quizzes: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function showQuiz($quiz_id)
    {
        try {
            $user = Auth::user();

            if (!$user->hasRole('student')) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $quiz = Quiz::with(['questions.options', 'questions.matchingPairs', 'classes', 'subject'])
                ->whereHas('classes.students', function ($query) use ($user) {
                    $query->where('student_id', $user->id);
                })
                ->where('is_published', true)
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->findOrFail($quiz_id);

            return response()->json($quiz, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Quiz not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching quiz: ' . $e->getMessage());
            Log::error('Error details: ' . $e->getTraceAsString());
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function submitQuiz(Request $request, Quiz $quiz)
    {
        try {
            $validated = $request->validate([
                'quiz_id' => 'required|exists:quizzes,id',
                'answers' => 'required|array',
                'answers.*.question_id' => 'required|exists:questions,id',
                'answers.*.student_answer' => 'nullable',
            ]);

            $user = Auth::user();

            // Ensure the user is a student, is assigned to the quiz, and the quiz is published and within the time limit
            if (!$user->hasRole('student') || !$this->isStudentEnrolledInQuiz($user->id, $quiz) || !$quiz->is_published || now()->isBefore($quiz->start_time) || now()->isAfter($quiz->end_time)) {
                return response()->json(['message' => 'Unauthorized or quiz not available'], 403);
            }

            $quizAttempt = QuizAttempt::create([
                'quiz_id' => $validated['quiz_id'],
                'student_id' => $user->id,
                'score' => 0, // Initial score
                'expires_at' => now()->addMinutes($quiz->time_limit),
            ]);

            foreach ($validated['answers'] as $answerData) {
                $answer = new Answer([
                    'question_id' => $answerData['question_id'],
                    'student_answer' => $answerData['student_answer'],
                    'is_correct' => false, // Initial value
                    'marks_obtained' => 0, // Initial value
                ]);

                // Attach the answer to the quiz attempt
                $quizAttempt->answers()->save($answer);
            }

            // Auto-grade the quiz
            $this->autoGrade($quizAttempt);

            // Calculate the score
            $totalScore = $quizAttempt->answers->sum('marks_obtained');
            $quizAttempt->update(['score' => $totalScore]);

            return response()->json(['message' => 'Quiz submitted successfully', 'score' => $totalScore], 201);
        } catch (\Exception $e) {
            Log::error('Error submitting quiz: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    // Auto-grading logic
    public function autoGrade(QuizAttempt $quizAttempt)
    {
        $totalScore = 0;

        foreach ($quizAttempt->answers as $answer) {
            $question = $answer->question;
            $score = 0;

            switch ($question->type) {
                case 'mcq':
                    $score = ($answer->student_answer === $question->correct_answer) ? $question->marks : 0;
                    break;

                case 'short_answer':
                    $similarity = levenshtein(strtolower($answer->student_answer), strtolower($question->correct_answer));
                    $maxLength = max(strlen($answer->student_answer), strlen($question->correct_answer));
                    $accuracy = 1 - ($similarity / $maxLength);
                    $score = ($accuracy >= 0.8) ? $question->marks : ($accuracy >= 0.5 ? $question->marks * 0.5 : 0);
                    break;

                case 'matching':
                    $correctPairs = MatchingPair::where('question_id', $question->id)->pluck('right_value', 'left_value');
                    $totalPairs = count($correctPairs);
                    $correctCount = 0;

                    foreach ($answer->student_answer as $left => $right) {
                        if (isset($correctPairs[$left]) && $correctPairs[$left] === $right) {
                            $correctCount++;
                        }
                    }
                    $score = ($correctCount / $totalPairs) * $question->marks;
                    break;
            }

            $answer->update([
                'is_correct' => $score > 0,
                'marks_obtained' => $score,
            ]);

            $totalScore += $score;
        }

        $quizAttempt->update(['score' => $totalScore]);
    }

    public function index()
    {
        try {
            $user = Auth::user();

            if (!$user->hasRole('student')) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $submissions = QuizAttempt::where('student_id', Auth::id())->with('quiz')->get();

            return response()->json($submissions);
        } catch (\Exception $e) {
            Log::error('Error fetching submissions: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(QuizAttempt $submission)
    {
        try {
            $user = Auth::user();

            if ($user->id !== $submission->student_id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $submission->load('quiz.questions', 'answers');

            return response()->json($submission);
        } catch (\Exception $e) {
            Log::error('Error fetching submission: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    private function isStudentEnrolledInQuiz($studentId, Quiz $quiz)
    {
        return StudentSubject::where('student_id', $studentId)
            ->where('subject_id', $quiz->subject_id)
            ->whereIn('class_id', $quiz->classes->pluck('id'))
            ->exists();
    }
}
