<?php

namespace Database\Seeders;

use App\Domain\Prices\Models\Metal;
use Illuminate\Database\Seeder;

class MetalSeeder extends Seeder
{
    public function run(): void
    {
        $metals = [
            [
                'symbol' => 'XAU',
                'name' => 'Gold',
            ],
            [
                'symbol' => 'XAG',
                'name' => 'Silver',
            ],
            [
                'symbol' => 'XPT',
                'name' => 'Platinum',
            ],
            [
                'symbol' => 'XPD',
                'name' => 'Palladium',
            ],
        ];

        foreach ($metals as $metal) {
            Metal::query()->updateOrCreate(['symbol' => $metal['symbol']], ['name' => $metal['name']]);
        }
    }
}
