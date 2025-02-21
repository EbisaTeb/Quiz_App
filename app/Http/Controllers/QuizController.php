<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\TeacherSubjectClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class QuizController extends Controller
{
    /**
     * Fetch classes and subjects where user_id == teacher_id
     */
    public function getTeacherAssignments($userId)
    {
        $assignments = TeacherSubjectClass::with(['class', 'subject'])
            ->where('teacher_id', $userId)
            ->get();

        if ($assignments->isEmpty()) {
            return response()->json(['message' => 'No assignments found'], 404);
        }

        return response()->json($assignments);
    }

    /**
     * List all quizzes
     */
    public function index(Request $request)
    {
        $quizzes = Quiz::query()
            ->when(Auth::user()->hasRole('teacher'), fn($q) => $q->where('teacher_id', Auth::user()->id))
            ->when(Auth::user()->hasRole('student'), fn($q) => $q->whereHas('class.students', fn($q) => $q->where('student_id', Auth::user()->id)))
            ->with(['class', 'subject'])
            ->paginate(10);

        return response()->json($quizzes);
    }

    /**
     * Store a new quiz
     */
    public function store(Request $request)
    {
        // Ensure only teachers can create quizzes
        if (!Auth::user()->hasRole('teacher')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'class_id' => [
                'required',
                'array',
                Rule::exists('teacher_subject_class', 'class_id')->where('teacher_id', Auth::user()->id)
            ],
            'subject_id' => [
                'required',
                Rule::exists('teacher_subject_class', 'subject_id')->where('teacher_id', Auth::id())
            ],
            'time_limit' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $quizzes = [];
        foreach ($validated['class_id'] as $classId) {
            $quiz = Quiz::create([
                'title' => $validated['title'],
                'teacher_id' => Auth::id(),
                'class_id' => $classId,
                'subject_id' => $validated['subject_id'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'time_limit' => $validated['time_limit'],
            ]);
            $quizzes[] = $quiz->load('questions');
        }

        return response()->json($quizzes, 201);
    }

    /**
     * Show a specific quiz
     */
    public function show(Quiz $quiz)
    {
        $this->authorize('view', $quiz);
        return response()->json($quiz->load(['questions', 'class', 'subject']));
    }
    // dd($request->all());
    /**
     * Update a specific quiz
     */
    public function update(Request $request, Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'class_id' => [
                'sometimes',
                'required',
                'array', // Accept multiple class IDs
                Rule::exists('teacher_subject_class', 'class_id')->where('teacher_id', Auth::user()->id)
            ],
            'subject_id' => [
                'sometimes',
                'required',
                Rule::exists('teacher_subject_class', 'subject_id')->where('teacher_id', Auth::id())
            ],
            'time_limit' => 'sometimes|required|integer|min:1',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'sometimes|required|date|after:start_time',
        ]);

        // Delete existing quiz if necessary (optional)
        $quiz->delete();

        // Store new quizzes for each class_id
        $updatedQuizzes = [];
        foreach ($validated['class_id'] as $classId) {
            $newQuiz = Quiz::create([
                'title' => $validated['title'] ?? $quiz->title,
                'teacher_id' => Auth::id(),
                'class_id' => $classId,
                'subject_id' => $validated['subject_id'] ?? $quiz->subject_id,
                'start_time' => $validated['start_time'] ?? $quiz->start_time,
                'end_time' => $validated['end_time'] ?? $quiz->end_time,
                'time_limit' => $validated['time_limit'] ?? $quiz->time_limit,
            ]);
            $updatedQuizzes[] = $newQuiz->load('questions');
        }

        return response()->json($updatedQuizzes, 200);
    }

    /**
     * Delete a specific quiz
     */
    public function destroy(Quiz $quiz)
    {
        $this->authorize('delete', $quiz);

        $quiz->delete();

        return response()->json(['message' => 'Quiz deleted successfully'], 200);
    }
}
