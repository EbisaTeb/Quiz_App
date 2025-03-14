<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getStatistics()
    {
        $totalUsers = [
            'students' => User::whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })->count(),

            'teachers' => User::whereHas('roles', function ($query) {
                $query->where('name', 'teacher');
            })->count(),

            'admins' => User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->count(),
        ];

        $totalQuizzes = [
            'published' => Quiz::where('is_published', true)->count(),
            'unpublished' => Quiz::where('is_published', false)->count(),
        ];

        $quizAttemptRate = [
            'published' => QuizAttempt::whereHas('quiz', function ($query) {
                $query->where('is_published', true);
            })->count(),
            'pending' => QuizAttempt::whereHas('quiz', function ($query) {
                $query->where('is_published', false);
            })->count(),
        ];

        $topPerformingStudents = User::whereHas('roles', function ($query) {
            $query->where('name', 'student');
        })
            ->with('submissions')
            ->get()
            ->sortByDesc(function ($student) {
                return $student->submissions->avg('score');
            })
            ->take(5);

        $upcomingQuizzes = Quiz::where('is_published', true)
            ->whereBetween('start_time', [Carbon::now(), Carbon::now()->addDays(7)])
            ->get();

        return response()->json([
            'totalUsers' => $totalUsers,
            'totalQuizzes' => $totalQuizzes,
            'quizAttemptRate' => $quizAttemptRate,
            'topPerformingStudents' => $topPerformingStudents,
            'upcomingQuizzes' => $upcomingQuizzes,
        ]);
    }
}
