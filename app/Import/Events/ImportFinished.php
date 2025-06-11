<?php

namespace App\Import\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ImportFinished implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(public float $durationInSeconds)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('import-items');
    }

    public function broadcastAs(): string
    {
        return 'ImportFinished';
    }
}
