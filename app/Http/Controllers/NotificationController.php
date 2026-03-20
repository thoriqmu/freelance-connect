<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use App\Traits\ApiResponse;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function __construct(private NotificationService $notificationService) {}

    /**
     * Get user notifications
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $notifications = $this->notificationService->getUserNotifications(
                auth()->id(),
                $request->input('per_page', 20)
            );

            return $this->successResponse('Success', $notifications, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $id): JsonResponse
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification = $this->notificationService->markAsRead($notification, auth()->id());

            return $this->successResponse('Notification marked as read', $notification, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $this->notificationService->markAllAsRead(auth()->id());

            return $this->successResponse('All notifications marked as read', null, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
