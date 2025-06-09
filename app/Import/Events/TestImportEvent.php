<?php

namespace App\Import\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;


class TestImportEvent implements ShouldBroadcast
{
    use SerializesModels, InteractsWithSockets, Dispatchable;

    public int $processed;
    public int $total;

    public function __construct(int $processed, int $total)
    {
        $this->processed = $processed;
        $this->total = $total;
    }

    public function broadcastOn()
    {
        return new Channel('import-progress');
    }

    public function broadcastAs()
    {
        return 'ImportProgressUpdated';
    }

    public function broadcastWith() {
        return [
            'message' => 'Test Import',
            'processed' => $this->processed,
            'total' => $this->total,
        ];
    }
}
