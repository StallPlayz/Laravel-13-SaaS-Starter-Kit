<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class AdminDataUpdated implements ShouldBroadcastNow
{
    use Dispatchable;

    public function __construct(
        public string $type,
        public ?array $payload = null
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin.health')];
    }

    public function broadcastAs(): string
    {
        return 'AdminDataUpdated';
    }
}
