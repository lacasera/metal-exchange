<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class SavingsPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'metal' => new MetalResource($this->whenLoaded('metal')),
            'executions' => SavingsPlanExecutionResource::collection($this->whenLoaded('executions')),
            'amount_eur' => $this->amount_eur,
            'frequency' => $this->frequency->value,
            'status' => $this->status,
            'last_execution_date' => $this->last_execution_date,
            'next_execution_date' => $this->next_execution_date,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
