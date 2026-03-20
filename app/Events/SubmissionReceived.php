<?php

namespace App\Events;

use App\Models\Submission;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubmissionReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Submission $submission) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("user.{$this->submission->project->client_id}");
    }

    public function broadcastAs(): string
    {
        return 'submission.received';
    }
}
