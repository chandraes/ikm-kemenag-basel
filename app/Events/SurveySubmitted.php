<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SurveySubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct()
    {
        //
    }

    /**
     * Mendapatkan channel tempat event akan disiarkan.
     */
    public function broadcastOn(): array
    {
        // Kita akan menyiarkan di channel publik bernama 'dashboard'
        return [
            new Channel('dashboard'),
        ];
    }
}
