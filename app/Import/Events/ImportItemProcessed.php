<?php

namespace App\Import\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportItemProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $items;

    /**
     * @param array $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('import-items');
    }

    public function broadcastAs(): string
    {
        return 'ImportItemProcessed';
    }

    public function broadcastWith(): array
    {
        return [
            'items' => $this->items,
        ];
    }
}
