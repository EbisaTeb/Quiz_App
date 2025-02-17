<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Subject;

class TeacherControllerssss extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('manageTeachers', User::class);

        $teachers = User::with('taughtSubjects')->get();
        return response()->json($teachers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('manageTeachers', User::class);

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'teacher_id' => 'required|exists:users,id'
        ]);

        $teacher = User::findOrFail($validated['teacher_id']);
        $teacher->taughtSubjects()->attach($validated['subject_id'], [
            'class_id' => $validated['class_id'],
            'teacher_id' => $validated['teacher_id']
        ]);

        return response()->json(['message' => 'Assignment successful']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $this->authorize('manageTeachers', User::class);

        $teacher = User::with('taughtSubjects')->findOrFail($id);
        return response()->json($teacher);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->authorize('manageTeachers', User::class);

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
        ]);

        $teacher = User::findOrFail($id);

        $teacher->taughtSubjects()->updateExistingPivot($validated['subject_id'], [
            'class_id' => $validated['class_id']
        ]);

        return response()->json(['message' => 'Assignment updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, Request $request)
    {
        $this->authorize('manageTeachers', User::class);

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id'
        ]);

        $teacher = User::findOrFail($id);
        $teacher->taughtSubjects()->detach($validated['subject_id']);

        return response()->json(['message' => 'Assignment removed successfully']);
    }

    /**
     * Fetch all users with the role of 'Teacher'.
     */
    public function fetchTeacher()
    {
        $this->authorize('manageTeachers', User::class);

        $teachers = User::with('roles')->whereHas('roles', function ($query) {
            $query->where('name', 'Teacher');
        })->get();

        return response()->json($teachers);
    }
}
