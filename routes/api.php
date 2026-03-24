<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SavedJobController;

Route::prefix('v1')->group(function () {
    // Auth Routes (Public)
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Profile
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'show']);
            Route::put('/', [ProfileController::class, 'update']);
            Route::get('/{userId}', [ProfileController::class, 'viewPublicProfile']);
            Route::put('/client', [ProfileController::class, 'updateClientProfile']);
            Route::put('/freelancer', [ProfileController::class, 'updateFreelancerProfile']);
        });

        // Projects
        Route::prefix('projects')->group(function () {
            Route::get('/', [ProjectController::class, 'index']);
            Route::get('/my', [ProjectController::class, 'myProjects'])->middleware('role:client');
            Route::get('/freelancer/my', [ProjectController::class, 'freelancerJobs'])->middleware('role:freelancer');
            Route::post('/', [ProjectController::class, 'store'])->middleware('role:client');
            Route::get('/{id}', [ProjectController::class, 'show']);
            Route::put('/{id}', [ProjectController::class, 'update'])->middleware('role:client');
            Route::delete('/{id}', [ProjectController::class, 'destroy'])->middleware('role:client');
            Route::patch('/{id}/archive', [ProjectController::class, 'archive'])->middleware('role:client');
            Route::patch('/{id}/unarchive', [ProjectController::class, 'unarchive'])->middleware('role:client');

            // Project Attachments
            Route::post('/{projectId}/attachments', [ProjectController::class, 'uploadAttachment'])->middleware('role:client');
            Route::delete('/{projectId}/attachments/{attachmentId}', [ProjectController::class, 'deleteAttachment'])->middleware('role:client');

            // Proposals
            Route::prefix('/{projectId}/proposals')->group(function () {
                Route::get('/', [ProposalController::class, 'projectProposals'])->middleware('role:client');
                Route::post('/', [ProposalController::class, 'store'])->middleware('role:freelancer');
                Route::get('/check', [ProposalController::class, 'check'])->middleware('role:freelancer');
                Route::patch('/{proposalId}/accept', [ProposalController::class, 'accept'])->middleware('role:client');
                Route::patch('/{proposalId}/reject', [ProposalController::class, 'reject'])->middleware('role:client');
            });

            // Submissions
            Route::prefix('/{projectId}/submissions')->group(function () {
                Route::get('/', [SubmissionController::class, 'projectSubmissions']);
                Route::post('/', [SubmissionController::class, 'store'])->middleware('role:freelancer');
                Route::patch('/{submissionId}/approve', [SubmissionController::class, 'approve'])->middleware('role:client');
                Route::patch('/{submissionId}/request-revision', [SubmissionController::class, 'requestRevision'])->middleware('role:client');
            });

            // Messages (Chat)
            Route::prefix('/{projectId}/messages')->group(function () {
                Route::get('/', [MessageController::class, 'index']);
                Route::post('/', [MessageController::class, 'store']);
            });

            // Reviews
            Route::post('/{projectId}/reviews', [ReviewController::class, 'store']);

            // Payments
            Route::get('/{projectId}/payment', [PaymentController::class, 'show']);
            Route::post('/{projectId}/payment/process', [PaymentController::class, 'process'])->middleware('role:client');
        });

        // Payments
        Route::prefix('payments')->group(function () {
            Route::get('/', [PaymentController::class, 'myPayments'])->middleware('role:client');
            Route::get('/{id}', [PaymentController::class, 'detail']);
        });

        // Dashboard Stats
        Route::get('/client/stats', [DashboardController::class, 'clientStats'])->middleware('role:client');
        Route::get('/freelancer/stats', [DashboardController::class, 'freelancerStats'])->middleware('role:freelancer');

        // Proposals (Freelancer only)
        Route::prefix('proposals')->group(function () {
            Route::get('/my', [ProposalController::class, 'myProposals'])->middleware('role:freelancer');
            Route::get('/{id}', [ProposalController::class, 'show']);
        });

        // Submissions
        Route::prefix('submissions')->group(function () {
            Route::get('/{id}', [SubmissionController::class, 'show']);
        });

        // Reviews
        Route::prefix('reviews')->group(function () {
            Route::get('/{id}', [ReviewController::class, 'show']);
            Route::get('/users/{userId}', [ReviewController::class, 'userReviews']);
        });

        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        });

        // Saved Jobs (Freelancer only)
        Route::prefix('saved-jobs')->middleware('role:freelancer')->group(function () {
            Route::get('/', [SavedJobController::class, 'index']);
            Route::post('/', [SavedJobController::class, 'store']);
            Route::get('/{projectId}/check', [SavedJobController::class, 'isSaved']);
            Route::delete('/{projectId}', [SavedJobController::class, 'destroy']);
        });
    });
});
