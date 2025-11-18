<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class LatestPricesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'symbols' => ['nullable', 'array', 'max:20'],
            'symbols.*' => ['string', 'max:10', 'regex:/^[A-Z]{2,10}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'symbols.array' => 'Symbols must be an array.',
            'symbols.max' => 'Maximum 20 symbols allowed.',
            'symbols.*.string' => 'Each symbol must be a string.',
            'symbols.*.max' => 'Each symbol must not exceed 10 characters.',
            'symbols.*.regex' => 'Each symbol must be 2-10 uppercase letters (e.g., XAU, XAG).',
        ];
    }

    /**
     * @return array<string>
     */
    public function getSymbols(): array
    {
        return $this->input('symbols', []);
    }
}
