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
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('category', 100)->nullable();
            $table->string('building_id', 5)->nullable();
            $table->integer('floor')->nullable();
            $table->string('room_id', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repair_equipments', function (Blueprint $table) {
            $table->dropColumn(['brand', 'model', 'category', 'building_id', 'floor', 'room_id']);
        });
    }
};
