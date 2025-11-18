<?php

declare(strict_types=1);

namespace App\Http\Requests\Trades;

use Illuminate\Foundation\Http\FormRequest;

final class SellMetalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'metal_id' => ['required', 'exists:metals,id'],
            'amount' => ['required', 'numeric', 'min:1'],
        ];
    }
}
