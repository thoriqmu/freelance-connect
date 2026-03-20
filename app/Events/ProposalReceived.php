<?php

namespace App\Events;

use App\Models\Proposal;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProposalReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Proposal $proposal) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("user.{$this->proposal->project->client_id}");
    }

    public function broadcastAs(): string
    {
        return 'proposal.received';
    }
}
