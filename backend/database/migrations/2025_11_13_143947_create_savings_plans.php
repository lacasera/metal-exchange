<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_plans', function (Blueprint $table): void {

            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('metal_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('frequency');
            $table->decimal('amount_eur', 12, 2);
            $table->string('status')->default('active');
            $table->timestamp('last_executed_at')->nullable();
            $table->timestamp('next_execution_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_plans');
    }
};
