<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PortfolioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'metal' => $this->resource['metal'],
            'metal_name' => $this->resource['metal_name'],
            'holdings' => $this->resource['holdings'],
        ];
    }
}
