<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Subject;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TeacherSubjectClass;
use Illuminate\Validation\ValidationException;

class TeacherController extends Controller
{
    /*
    *
     *  fetch the class and subject a listing of the resource.
     */

    public function fechSubjectClass()
    {
        $this->authorize('manageTeachers', User::class);

        // Fetch all classes and subjects
        $classes = ClassGroup::all();
        $subjects = Subject::all();

        return response()->json([
            'classes' => $classes,
            'subjects' => $subjects
        ]);
    }

    // search the teacher
    public function searchTeacher(Request $request)
    {
        $this->authorize('manageTeachers', User::class);
        $search = $request->input('search');
        $teacher = User::whereHas('roles', function ($query) {
            $query->where('name', 'teacher');
        })
            ->where('email', 'like', '%' . $search . '%')
            ->get();
        return response()->json($teacher);
    }

    // Store a newly created resource in storage.

    public function store(Request $request)
    {
        $this->authorize('manageTeachers', User::class);
        try {
            $validated = $request->validate([
                'subject_id' => 'required|exists:subjects,id',
                'class_id' => 'required|exists:classes,id',
                'teacher_id' => 'required|exists:users,id'
            ]);

            $teacher = TeacherSubjectClass::Create($validated);
            return response()->json($teacher, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Update the specified resource.
     */
    public function update(Request $request, TeacherSubjectClass $teacher)
    {
        $this->authorize('manageTeachers', User::class);
        try {
            $validated = $request->validate([
                'teacher_id' => 'required|exists:users,id',
                'subject_id' => 'required|exists:subjects,id',
                'class_id' => 'required|exists:classes,id',
            ]);
            $teacher->update($validated);
            return response()->json(['message' => 'Assignment updated successfully']);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function show($id)
    {
        $this->authorize('manageTeachers', User::class);

        try {
            // $teacher = TeacherSubjectClass::all();

            $teacherSubjectClasses = TeacherSubjectClass::with(['subject', 'class', 'teacher'])->get();
            return response()->json($teacherSubjectClasses, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Remove the specified resource from storage.
    public function destroy(TeacherSubjectClass $teacher)

    {
        $this->authorize('manageTeachers', User::class);
        try {
            $teacher->delete();
            return response()->json(['message' => 'Assignment removed successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
