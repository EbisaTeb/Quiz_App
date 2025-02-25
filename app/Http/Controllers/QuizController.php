<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\TeacherSubjectClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class QuizController extends Controller
{
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

    public function index(Request $request)
    {
        $quizzes = Quiz::query()
            ->when(Auth::user()->hasRole('teacher'), fn($q) => $q->where('teacher_id', Auth::user()->id))
            ->when(Auth::user()->hasRole('student'), fn($q) => $q->whereHas('classes.students', fn($q) => $q->where('student_id', Auth::user()->id)))
            ->with(['classes', 'subject'])
            ->paginate(10);

        return response()->json($quizzes);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('teacher')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'class_ids' => [
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

            $quiz = Quiz::create([
                'title' => $validated['title'],
                'teacher_id' => Auth::id(),
                'subject_id' => $validated['subject_id'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'time_limit' => $validated['time_limit'],
            ]);

            $quiz->classes()->attach($validated['class_ids']);

            return response()->json($quiz->load('questions'), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Quiz $quiz)
    {
        $this->authorize('view', $quiz);
        return response()->json($quiz->load(['questions', 'classes', 'subject']));
    }

    public function update(Request $request, Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'class_ids' => [
                'sometimes',
                'required',
                'array',
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

        $quiz->update($validated);

        if (isset($validated['class_ids'])) {
            $quiz->classes()->sync($validated['class_ids']);
        }

        return response()->json($quiz->load('questions'), 200);
    }

    public function destroy(Quiz $quiz)
    {
        $this->authorize('delete', $quiz);

        $quiz->delete();

        return response()->json(['message' => 'Quiz deleted successfully'], 200);
    }
}
