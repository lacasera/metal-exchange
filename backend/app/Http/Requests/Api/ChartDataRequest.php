<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class ChartDataRequest extends FormRequest
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
            'symbol' => ['required', 'string', 'in:XAU,XAG,XPT,XPD'],
            'start_date' => ['required', 'date_format:Y-m-d\TH:i:s\Z'],
            'interval' => ['required', 'string', 'in:1m,5m,15m,30m,1h,4h,1d,1w,30d,90d,365d'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'symbol.required' => 'Metal symbol is required.',
            'symbol.in' => 'Metal symbol must be one of: XAU (Gold), XAG (Silver), XPT (Platinum), XPD (Palladium).',
            'start_date.required' => 'Start date is required.',
            'start_date.date_format' => 'Start date must be in ISO 8601 format (Y-m-d\TH:i:s\Z).',
            'interval.required' => 'Interval is required.',
            'interval.in' => 'Interval must be one of: 1m, 5m, 15m, 30m, 1h, 4h, 1d, 1w, 30d, 90d, 365d.',
        ];
    }

    public function getSymbol(): string
    {
        return $this->input('symbol');
    }

    public function getStartDate(): string
    {
        return $this->input('start_date');
    }

    public function getInterval(): string
    {
        return $this->input('interval');
    }
}
