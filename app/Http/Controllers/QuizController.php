<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpFoundation\Response;

class QuizController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();

            return Quiz::when($user->hasRole('teacher'), function ($query) {
                return $query->where('created_by', Auth::id())
                    ->withCount(['questions', 'students', 'submissions']);
            })
                ->when($user->hasRole('admin'), function ($query) {
                    return $query->with(['creator', 'students', 'submissions']);
                })
                ->paginate(10);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve quizzes',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'required|date|after:now',
                'end_time' => 'required|date|after:start_time',
                'duration' => 'required|integer|min:1',
                'student_ids' => 'sometimes|array',
                'student_ids.*' => 'exists:users,id'
            ]);

            return DB::transaction(function () use ($validated) {
                try {
                    $quiz = Quiz::create($validated + [
                        'created_by' => Auth::id(),
                        'is_published' => false
                    ]);

                    if (isset($validated['student_ids'])) {
                        $this->validateStudents($validated['student_ids']);
                        $quiz->students()->sync($validated['student_ids']);
                    }

                    return response()->json($quiz->load('students'), Response::HTTP_CREATED);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to create quiz',
                        'error' => $e->getMessage()
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            });
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Quiz $quiz)
    {
        try {
            $this->authorize('view', $quiz);

            return $quiz->load([
                'questions.options',
                'students:id,name',
                'submissions' => fn($q) => $q->with('student:id,name')
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'Unauthorized to view this quiz'
            ], Response::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve quiz',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, Quiz $quiz)
    {
        try {
            $this->authorize('update', $quiz);

            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'sometimes|date|after:now',
                'end_time' => 'sometimes|date|after:start_time',
                'duration' => 'sometimes|integer|min:1',
                'student_ids' => 'sometimes|array',
                'student_ids.*' => 'exists:users,id'
            ]);

            return DB::transaction(function () use ($quiz, $validated) {
                try {
                    $quiz->update($validated);

                    if (isset($validated['student_ids'])) {
                        $this->validateStudents($validated['student_ids']);
                        $quiz->students()->sync($validated['student_ids']);
                    }

                    return response()->json($quiz->fresh()->load('students'));
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to update quiz',
                        'error' => $e->getMessage()
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            });
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'Unauthorized to update this quiz'
            ], Response::HTTP_FORBIDDEN);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Quiz $quiz)
    {
        try {
            $this->authorize('delete', $quiz);
            $quiz->delete();
            return response([
                'message' => 'Quiz deleted successfully'
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'Unauthorized to delete this quiz'
            ], Response::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete quiz',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function togglePublish(Quiz $quiz)
    {
        try {
            $this->authorize('update', $quiz);

            throw_if(
                $quiz->questions()->count() < 1,
                new \RuntimeException('Cannot publish quiz without questions')
            );

            $quiz->update(['is_published' => !$quiz->is_published]);

            return response()->json([
                'message' => $quiz->is_published
                    ? 'Quiz published successfully'
                    : 'Quiz unpublished successfully'
            ]);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => 'Unauthorized to modify this quiz'
            ], Response::HTTP_FORBIDDEN);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to toggle quiz status',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function validateStudents(array $studentIds)
    {
        try {
            $validStudents = Auth::user()->students()
                ->whereIn('id', $studentIds)
                ->pluck('id')
                ->toArray();

            if (count(array_diff($studentIds, $validStudents)) > 0) {
                throw new \InvalidArgumentException('Contains invalid student assignments');
            }
        } catch (\InvalidArgumentException $e) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }
}
