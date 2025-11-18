<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_plan_executions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('savings_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('metal_id')->constrained()->cascadeOnDelete();
            $table->decimal('metal_price_eur', 12, 4);
            $table->decimal('amount_eur', 12, 2);
            $table->decimal('metal_quantity', 18, 6);
            $table->timestamp(column: 'executed_at')->index();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_plan_executions');
    }
};
