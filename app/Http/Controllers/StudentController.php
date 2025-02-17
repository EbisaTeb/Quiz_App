<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ClassGroup;
use App\Models\StudentSubject;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    /*
    *
     *  fetch the class and subject a listing of the resource.
     */

    public function getSubjectClass()
    {
        $this->authorize('manageStudents', User::class);

        // Fetch all classes and subjects
        $classes = ClassGroup::all();
        $subjects = Subject::all();

        return response()->json([
            'classes' => $classes,
            'subjects' => $subjects
        ]);
    }

    // search the teacher
    public function searchStudent(Request $request)
    {
        $this->authorize('manageStudents', User::class);
        $search = $request->input('search');
        $teacher = User::whereHas('roles', function ($query) {
            $query->where('name', 'student');
        })
            ->where('email', 'like', '%' . $search . '%')
            ->get();
        return response()->json($teacher);
    }

    // Store a newly created resource in storage.
    public function getStudentClasses()
    {
        $this->authorize('manageStudents', User::class);

        try {
            $teacherSubjectClasses = StudentSubject::with(['subject', 'class', 'student'])->get();
            return response()->json($teacherSubjectClasses, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Store a newly created resource in storage.
    public function storeStudentClasses(Request $request)
    {
        $this->authorize('manageStudents', User::class);

        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:users,id',
                'class_id' => 'required|exists:classes,id',
                'subject_id' => 'required|array',
                'subject_id.*' => 'exists:subjects,id'
            ]);

            $studentId = $validated['student_id'];
            $classId = $validated['class_id'];
            $subjectIds = $validated['subject_id'];

            foreach ($subjectIds as $subjectId) {
                StudentSubject::create([
                    'student_id' => $studentId,
                    'class_id' => $classId,
                    'subject_id' => $subjectId
                ]);
            }

            return response()->json(['message' => 'Assignments created successfully'], 201);
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

    // Update the specified resource in storage.
    public function updateStudentClasses(Request $request, StudentSubject $studentSubject)
    {
        $this->authorize('manageStudents', User::class);

        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:users,id',
                'class_id' => 'required|exists:classes,id',
                'subject_id' => 'required|array',
                'subject_id.*' => 'exists:subjects,id'
            ]);

            $studentId = $validated['student_id'];
            $classId = $validated['class_id'];
            $subjectIds = $validated['subject_id'];

            // Delete existing assignments for the student and class
            StudentSubject::where('student_id', $studentId)->where('class_id', $classId)->delete();

            // Create new assignments
            foreach ($subjectIds as $subjectId) {
                StudentSubject::create([
                    'student_id' => $studentId,
                    'class_id' => $classId,
                    'subject_id' => $subjectId
                ]);
            }

            return response()->json(['message' => 'Assignments updated successfully'], 200);
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

    // Remove the specified resource from storage.
    public function destroyStudentClasses(StudentSubject $studentSubject)
    {
        $this->authorize('manageStudents', User::class);
        try {
            $studentSubject->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
