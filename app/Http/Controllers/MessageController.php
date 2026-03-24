<?php

namespace App\Http\Controllers;

use App\Services\MessageService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    use ApiResponse;

    public function __construct(private MessageService $messageService) {}

    /**
     * Get project messages (chat history)
     */
    public function index(int $projectId, Request $request): JsonResponse
    {
        try {
            $project = \App\Models\Project::findOrFail($projectId);
            $user = auth()->user();

            // Check authorization
            $isAuthorized = $project->client_id === $user->clientProfile?->id ||
                           $project->freelancer_id === $user->freelancerProfile?->id;

            if (!$isAuthorized) {
                return $this->forbiddenResponse();
            }

            $messages = $this->messageService->getProjectMessages(
                $projectId,
                $request->input('per_page', 15)
            );

            return $this->successResponse('Success', $messages, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Send message
     */
    public function store(int $projectId, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'content' => 'required|string|min:1'
            ]);

            $project = \App\Models\Project::findOrFail($projectId);
            $user = auth()->user();

            $isAuthorized = $project->client_id === $user->clientProfile?->id ||
                            $project->freelancer_id === $user->freelancerProfile?->id;

            if (!$isAuthorized) {
                return $this->forbiddenResponse();
            }

            $message = $this->messageService->sendMessage(
                $validated,
                $projectId,
                $user->id
            );

            return $this->successResponse('Message sent successfully', $message, 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}
