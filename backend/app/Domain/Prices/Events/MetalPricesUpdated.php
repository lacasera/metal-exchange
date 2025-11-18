<?php

declare(strict_types=1);

namespace App\Domain\Prices\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class MetalPricesUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public array $prices) {}

    public function broadcastOn(): Channel
    {
        return new Channel('metal-prices');
    }

    public function broadcastWith(): array
    {
        return [
            'prices' => $this->prices,
        ];
    }
}
