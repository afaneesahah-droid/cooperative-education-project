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
        if (!Schema::hasTable('budget_sources')) {
            Schema::create('budget_sources', function (Blueprint $table) {
                $table->id();
                $table->string('source_key')->unique();
                $table->string('source_name');
                $table->decimal('total_allocated', 15, 2)->default(0.00);
                $table->string('fiscal_year')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_sources');
    }
};
