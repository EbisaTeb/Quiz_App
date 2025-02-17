<?php

namespace App\Http\Controllers;

use App\Models\ClassGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ClassController extends Controller
{
    public function index()
    {
        $this->authorize('managementClass', User::class);

        try {
            $classes = ClassGroup::withCount([
                'students',  // Count students in the class
                'subjects', // Count subjects in the class
                'teachers'  // Count teachers in the class
            ])
                ->with([
                    'students:id,name,email',  // Get student details
                    'subjects:id,name',        // Get subject details
                    'teachers:id,name,email'   // Get teacher details
                ])
                ->get();

            return response()->json($classes, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request)
    {
        $this->authorize('managementClass', User::class);
        try {
            $validated = $request->validate([
                'name' => 'required|unique:classes|max:255',
                'grade_level' => 'required|string|max:50',
                'year' => 'required|string|max:20' //  Added validation for year
            ]);

            $class = ClassGroup::create($validated);

            return response()->json($class, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create class',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, ClassGroup $class)
    {
        $this->authorize('managementClass', User::class);
        try {
            $validated = $request->validate([
                'name' => 'sometimes|unique:classes,name,' . $class->id . '|max:255',
                'grade_level' => 'sometimes|string|max:50',
                'year' => 'sometimes|string|max:20' // Added validation for year
            ]);

            $class->update($validated);

            return response()->json($class);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update class',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function destroy(ClassGroup $class)
    {
        $this->authorize('managementClass', User::class);
        try {
            $class->delete();
            return response()->json([
                'message' => 'Class deleted successfully'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete class',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function attachSubjects(Request $request, ClassGroup $class)
    {
        $this->authorize('managementClass', User::class);
        $request->validate([
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'exists:subjects,id'
        ]);

        $class->subjects()->sync($request->subject_ids);
    }
}
