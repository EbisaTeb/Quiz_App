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
    //fetch active quiz for student
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
    // show Activate quiz question  to student
    public function showQuiz($quiz_id)
    {
        try {
            $user = Auth::user();

            // Log user details for debugging
            Log::info('Authenticated user:', ['user' => $user]);

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

    // take the quiz by stuednt
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
                'score' => null, // Initial score
                'expires_at' => now()->addMinutes($quiz->time_limit),
            ]);

            foreach ($validated['answers'] as $answerData) {
                $answer = new Answer([
                    'question_id' => $answerData['question_id'],
                    'student_answer' => $answerData['student_answer'],
                    'is_correct' => false, // Initial value
                    'marks_obtained' => null,  // Null for short answer
                ]);

                // Attach the answer to the quiz attempt
                $quizAttempt->answers()->save($answer);
            }

            // Auto-grade the quiz
            $this->autoGrade($quizAttempt);

            // Calculate the score
            // NUll marks_obtained value for shortAnswer
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

                // case 'short_answer':
                //     $similarity = levenshtein(strtolower($answer->student_answer), strtolower($question->correct_answer));
                //     $maxLength = max(strlen($answer->student_answer), strlen($question->correct_answer));
                //     $accuracy = 1 - ($similarity / $maxLength);
                //     $score = ($accuracy >= 0.8) ? $question->marks : ($accuracy >= 0.5 ? $question->marks * 0.5 : 0);
                //     break;
                case 'short_answer':
                    // Mark for teacher review, don't auto-grade
                    $score = null;
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
                'is_correct' => $score ? $score > 0 : false,
                'marks_obtained' => $score,
            ]);

            if ($score !== null) {
                $totalScore += $score;
            }
        }

        $quizAttempt->update(['score' => $totalScore]);
    }

    // show student his submission
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
    // show student submission details
    public function show(QuizAttempt $submission)
    {
        try {
            $user = Auth::user();

            if ($user->hasRole('admin')) {
                // Admin can view all submissions
                $submission->load('quiz.questions', 'answers');
                return response()->json($submission);
            } elseif ($user->hasRole('teacher')) {
                // Teacher can view submissions for their quizzes only
                if ($submission->quiz->teacher_id !== $user->id) {
                    return response()->json(['message' => 'Unauthorized'], 403);
                }
                $submission->load('quiz.questions', 'answers');
                return response()->json($submission);
            } elseif ($user->id === $submission->student_id) {
                // Student can view their own submissions
                $submission->load('quiz.questions', 'answers');
                return response()->json($submission);
            } else {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching submission: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }


    // teacher see student score
    public function teacherSeeStudentscore($quiz_id)
    {
        try {
            $user = Auth::user();

            if (!$user->hasRole('teacher')) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $quiz = Quiz::findOrFail($quiz_id);

            if ($quiz->teacher_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $submissions = QuizAttempt::where('quiz_id', $quiz_id)
                ->with('student:id,name') // Assuming the student model has 'id' and 'name' attributes
                ->get(['id', 'student_id', 'score']); // Include 'id' for submission ID

            return response()->json($submissions);
        } catch (\Exception $e) {
            Log::error('Error fetching student scores: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    // teacher release the total result
    public function updateMarkRelease(Request $request, Answer $attempt)
    {
        $validated = $request->validate([
            'is_published' => 'required|boolean',
        ]);

        $attempt->is_published = $validated['is_published'];
        $attempt->save();

        return response()->json(['message' => 'Score release updated successfully']);
    }


    // admin see student score
    public function adminSeeStudentscore($quiz_id)
    {
        try {
            $user = Auth::user();

            if (!$user->hasRole('admin')) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $quiz = Quiz::findOrFail($quiz_id);

            $submissions = QuizAttempt::where('quiz_id', $quiz_id)
                ->with('student:id,name') // Assuming the student model has 'id' and 'name' attributes
                ->get(['id', 'student_id', 'score']); // Include 'id' for submission ID

            return response()->json($submissions);
        } catch (\Exception $e) {
            Log::error('Error fetching student scores: ' . $e->getMessage());
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
