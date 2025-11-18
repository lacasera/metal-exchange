<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metal_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('metal_id')->constrained()->cascadeOnDelete();
            $table->decimal('price_eur', 12, 4);
            $table->decimal('price_usd', 12, 4)->nullable();
            $table->timestamp('recorded_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metal_prices');
    }
};
