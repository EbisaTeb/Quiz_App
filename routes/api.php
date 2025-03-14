<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ShortAnswerScoring;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\DashboardController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:api', 'approved')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/submissions/{submission}', [SubmissionController::class, 'show']);

    // Admin routes
    Route::middleware('role:admin')->group(function () {

        // User management
        Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics']);
        Route::get('/admin/users', [AdminController::class, 'getAllUsers']);
        Route::get('/roles', [AdminController::class, 'getRoles']);
        Route::post('/users/{user}/roles', [AdminController::class, 'assignRole']);
        Route::get('/fetchteachers', [AdminController::class, 'fetchTeacher']);
        // User approval
        Route::get('/pending-approvals', [AdminController::class, 'pendingApprovals']);
        Route::post('/users/{user}/approve', [AdminController::class, 'approveUser']);

        // Quiz management
        Route::get('/admin/quizzes', [AdminController::class, 'getAllQuizzes']);
        Route::put('/admin/quizzes/{quiz}/status', [AdminController::class, 'updateQuizStatus']);
        Route::get('/admin/quizzes/{quiz_id}/student-scores', [SubmissionController::class, 'adminSeeStudentscore']);

        // Teacher routes
        Route::get('/teachers/subjects-classes', [TeacherController::class, 'fechSubjectClass']);
        Route::get('/teachers/search', [TeacherController::class, 'searchTeacher']);
        Route::put('/teachers/assignments/{teacher}', [TeacherController::class, 'update']);
        Route::post('/teachers/assignments', [TeacherController::class, 'store']);
        Route::get('/teachers/{id}', [TeacherController::class, 'show']);
        Route::delete('/teachers/assignments/{teacher}', [TeacherController::class, 'destroy']);


        // Student routes
        Route::get('/students/subjects-classes', [StudentController::class, 'getSubjectClass']);
        Route::get('/students/search', [StudentController::class, 'searchStudent']);
        Route::get('/students/classes', [StudentController::class, 'getStudentClasses']);
        Route::post('/students/classes', [StudentController::class, 'storeStudentClasses']);
        Route::put('/students/classes/{studentSubject}', [StudentController::class, 'updateStudentClasses']);
        Route::delete('/students/classes/{studentSubject}', [StudentController::class, 'destroyStudentClasses']);

        // Academic management
        Route::apiResource('subjects', SubjectController::class);
        Route::apiResource('classes', ClassController::class);
    });

    // Teacher routes
    Route::middleware('role:teacher')->group(function () {
        Route::apiResource('quizzes', QuizController::class)->except(['index', 'show']);
        Route::get('/quizzes/teacher-assignments/{userId}', [QuizController::class, 'getTeacherAssignments']);
        Route::get('/quizzes', [QuizController::class, 'index']);
        Route::get('/quizzes/{quiz}', [QuizController::class, 'show']);

        // fetching quiz with questions
        Route::get('quizzes/{quiz}/questions', [QuestionController::class, 'getQuizQuestions']);
        Route::get('quizzes/{quiz_id}/questions', [QuestionController::class, 'getQuizQuestions']);

        // Question routes
        Route::get('/quiz/teacher-quizzes', [QuestionController::class, 'getTeacherQuizzes']);
        Route::post('/questions', [QuestionController::class, 'addQuestions']);
        Route::put('/questions/{id}', [QuestionController::class, 'updateQuestion']);
        Route::delete('/questions/{id}', [QuestionController::class, 'deleteQuestion']);
        Route::get('/questions/{id}', [QuestionController::class, 'getQuestion']);

        Route::get('/teacher/quizzes', [ShortAnswerScoring::class, 'getTeacherQuizzes']);
        Route::get('/quiz/{quizId}/short-answer-submissions', [ShortAnswerScoring::class, 'getSubmissionShortAnswer']);
        Route::post('/submission/{submissionId}/question/{questionId}/score', [ShortAnswerScoring::class, 'updateShortAnswerScore']);
        Route::get('/teacher/quizzes/{quiz_id}/student-scores', [SubmissionController::class, 'teacherSeeStudentscore']);
        Route::post('/quizzes/{quiz_id}/release-score', [SubmissionController::class, 'updateScoreRelease']);
    });

    // Student routes
    Route::middleware('role:student')->group(function () {
        Route::get('/student/active-quizzes', [SubmissionController::class, 'fetchActiveQuizzes']);
        Route::get('/student/quizzes/{quiz}', [SubmissionController::class, 'showQuiz']);
        Route::post('/quizzes/{quiz}/submit', [SubmissionController::class, 'submitQuiz']);
        Route::post('/quizzes/auto-grade', [SubmissionController::class, 'autoGrade']);
        Route::get('/submissions', [SubmissionController::class, 'index']);
    });

    // Shared routes
    Route::get('/user', function (Request $request) {
        return $request->user()->load('roles');
    });
});
