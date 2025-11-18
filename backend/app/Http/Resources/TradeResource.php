<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'metal' => new MetalResource($this->whenLoaded('metal')),
            'action' => $this->action->value,
            'metal_price_eur' => $this->metal_price_eur,
            'amount_eur' => $this->amount_eur,
            'metal_quantity' => $this->metal_quantity,
            'executed_at' => $this->executed_at->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
