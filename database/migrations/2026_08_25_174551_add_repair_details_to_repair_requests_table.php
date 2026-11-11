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
        Schema::table('repair_requests', function (Blueprint $table) {
            $table->string('repair_evaluation')->nullable();
            $table->text('repair_details')->nullable();
            $table->text('repair_result')->nullable();
            $table->text('unrepairable_reason')->nullable();
            $table->string('technician_recommendation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repair_requests', function (Blueprint $table) {
            $table->dropColumn([
                'repair_evaluation',
                'repair_details',
                'repair_result',
                'unrepairable_reason',
                'technician_recommendation'
            ]);
        });
    }
};
