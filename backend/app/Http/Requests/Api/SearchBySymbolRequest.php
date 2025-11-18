<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class SearchBySymbolRequest extends FormRequest
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
            'symbol' => ['required', 'string', 'max:10', 'regex:/^[A-Z]{2,10}$/'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'symbol.required' => 'Metal symbol is required.',
            'symbol.regex' => 'Metal symbol must be 2-10 uppercase letters (e.g., XAU, XAG).',
            'size.integer' => 'Size must be an integer.',
            'size.min' => 'Size must be at least 1.',
            'size.max' => 'Size must not exceed 100.',
        ];
    }

    public function getSymbol(): string
    {
        return $this->route('symbol');
    }

    public function getSize(): int
    {
        return $this->input('size', 20);
    }
}
