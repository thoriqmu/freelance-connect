<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationService
{
    /**
     * Get user notifications
     */
    public function getUserNotifications(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return Notification::where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Send notification
     */
    public function send(int $userId, string $type, string $message, array $data = []): Notification
    {
        try {
            $notification = Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'message' => $message,
                'data' => $data,
            ]);

            Log::info('Notification sent', ['notification_id' => $notification->id, 'user_id' => $userId]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Send notification failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification, int $userId): Notification
    {
        try {
            if ($notification->user_id !== $userId) {
                throw new \Exception('You are not authorized to read this notification.', 403);
            }

            $notification->update(['read_at' => now()]);

            return $notification;
        } catch (\Exception $e) {
            Log::error('Mark notification as read failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(int $userId): bool
    {
        try {
            Notification::where('user_id', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return true;
        } catch (\Exception $e) {
            Log::error('Mark all notifications as read failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
