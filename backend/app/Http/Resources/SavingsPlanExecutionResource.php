<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class SavingsPlanExecutionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'savings_plan_id' => $this->savings_plan_id,
            'metal' => new MetalResource($this->whenLoaded('metal')),
            'amount_eur' => $this->amount_eur,
            'metal_price_eur_per_oz' => $this->metal_price_eur_per_oz,
            'metal_amount_oz' => $this->metal_amount_oz,
            'executed_at' => $this->executed_at->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
