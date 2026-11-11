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
        Schema::table('repair_equipments', function (Blueprint $table) {
            $table->string('supplier')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('warranty')->nullable();
            $table->date('purchase_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repair_equipments', function (Blueprint $table) {
            $table->dropColumn(['supplier', 'price', 'warranty', 'purchase_date']);
        });
    }
};
