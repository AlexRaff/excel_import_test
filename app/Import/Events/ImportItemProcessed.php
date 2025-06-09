<?php

namespace App\Import\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class ImportItemProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $id;
    public string $name;
    public string $date;

    public function __construct(int $id, string $name, string $date)
    {
        $this->id = $id;
        $this->name = $name;
        $this->date = $date;
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
            'id' => $this->id,
            'name' => $this->name,
            'date' => $this->date,
        ];
    }
}

