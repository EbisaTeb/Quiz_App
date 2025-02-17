<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class SubjectController extends Controller
{
    public function index()
    {
        try {
            return Subject::paginate(10);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve subjects',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        $this->authorize('managementSubject', User::class);
        try {
            $validated = $request->validate([
                'name' => 'required|unique:subjects|max:255'
            ]);

            $subject = Subject::create($validated);

            return response()->json($subject, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create subject',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Subject $subject)
    {
        try {
            return $subject->load('teachers');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve subject',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, Subject $subject)
    {
        $this->authorize('managementSubject', User::class);
        try {
            $validated = $request->validate([
                'name' => 'required|unique:subjects,name,' . $subject->id . '|max:255'
            ]);

            $subject->update($validated);

            return response()->json($subject);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update subject',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Subject $subject)
    {
        $this->authorize('managementSubject', User::class);
        try {
            $subject->delete();
            return response()->json([
                'message' => 'Subject deleted successfully'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete subject',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
