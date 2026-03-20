<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Project;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class MessageService
{
    /**
     * Get messages for a project
     */
    public function getProjectMessages(int $projectId, int $perPage = 15): LengthAwarePaginator
    {
        return Message::where('project_id', $projectId)
            ->with(['sender', 'project'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Send a message
     */
    public function sendMessage(array $data, int $projectId, int $senderId): Message
    {
        try {
            $project = Project::findOrFail($projectId);

            // Only client and assigned freelancer can chat
            $isAuthorized = $project->client_id === $senderId || $project->freelancer_id === $senderId;
            if (!$isAuthorized) {
                throw new \Exception('You are not part of this project.', 403);
            }

            $message = Message::create([
                'project_id' => $projectId,
                'sender_id' => $senderId,
                'content' => $data['content'],
            ]);

            Log::info('Message sent', ['message_id' => $message->id, 'project_id' => $projectId]);

            return $message;
        } catch (\Exception $e) {
            Log::error('Send message failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Mark message as read
     */
    public function markAsRead(Message $message): Message
    {
        try {
            $message->update(['read_at' => now()]);

            return $message;
        } catch (\Exception $e) {
            Log::error('Mark message as read failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
