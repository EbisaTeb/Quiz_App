<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    // Assign a role to a user

    public function assignRole(Request $request, User $user)
    {

        try {
            $validated = $request->validate([
                'role_id' => 'required|exists:roles,id'
            ]);

            $user->roles()->sync([$validated['role_id']]);

            return response()->json([
                'message' => 'Role updated successfully',
                'user' => $user->load('roles')
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to assign role',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getRoles()
    {
        try {
            return response()->json(Role::all());
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve roles',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Approve a user
    public function approveUser(User $user)
    {
        try {
            $user->update([
                'is_approved' => true,
                'approved_at' => now(),
                'approved_by' => Auth::id()
            ]);

            return response()->json(['message' => 'User approved successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Approval failed'], 500);
        }
    }

    public function pendingApprovals()
    {
        return User::where('is_approved', false)->get();
    }

    public function getAllUsers()
    {
        $this->authorize('viewAny', User::class); // Ensure only admins can access

        $users = User::all(); // Fetch all users

        return response()->json([
            'message' => 'Users retrieved successfully',
            'users' => $users
        ]);
    }
    //assign teacher to subject and class

    public function assignTeacherSubjects(Request $request, User $teacher)
    {
        $this->authorize('manageTeachers', User::class);

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'teacher_id' => 'required|exists:users,id '
        ]);

        $teacher->taughtSubjects()->attach($validated['subject_id'], [
            'class_id' => $validated['class_id'],
            'teacher_id' => $validated['teacher_id']
        ]);

        return response()->json(['message' => 'Assignment successful']);
    }

    public function assignStudentSubjects(Request $request, User $student)
    {
        $this->authorize('manageStudents', User::class);
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'exists:subjects,id'
        ]);

        foreach ($validated['subject_ids'] as $subjectId) {
            $student->enrollSubjectsStudent()->attach($subjectId, ['class_id' => $validated['class_id']]);
        }

        return response()->json(['message' => 'Subjects assigned successfully']);
    }

    public function getAllQuizzes()
    {
        $quizzes = Quiz::with(['teacher', 'subject'])->get();
        return response()->json($quizzes);
    }

    public function updateQuizStatus(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'is_published' => 'required|boolean',
        ]);

        $quiz->is_published = $validated['is_published'];
        $quiz->published_by = $validated['is_published'] ? Auth::id() : null;
        $quiz->save();

        return response()->json(['message' => 'Quiz status updated successfully']);
    }
}
