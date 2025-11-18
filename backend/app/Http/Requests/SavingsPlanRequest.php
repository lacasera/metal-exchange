<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Savings\Enums\SavingsPlanFrequency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SavingsPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'metal_id' => ['required', 'exists:metals,id'],
            'frequency' => ['required', Rule::in(SavingsPlanFrequency::values())],
            'amount_eur' => ['required', 'numeric', 'min:1'],
        ];
    }
}
