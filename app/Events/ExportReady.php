<?php
namespace App\Events;

use App\Models\Export;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExportReady implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public Export $export
    ) {}

    public function broadcastOn(): array
    {
        // Kirim ke channel privat milik user yang meminta
        return [new PrivateChannel('exports.' . $this->user->id)];
    }
}
