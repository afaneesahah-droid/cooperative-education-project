<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('department_budgets')) {
            Schema::create('department_budgets', function (Blueprint $table) {
                $table->id();
                $table->string('department');
                $table->string('fiscal_year', 20);
                $table->string('budget_type');
                $table->decimal('allocated_amount', 15, 2)->default(0.00);
                $table->text('description')->nullable();
                $table->timestamps();

                $table->unique(['department', 'fiscal_year', 'budget_type'], 'dept_year_budget_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_budgets');
    }
};
