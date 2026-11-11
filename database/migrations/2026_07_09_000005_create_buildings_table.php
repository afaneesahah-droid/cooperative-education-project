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
        Schema::create('buildings', function (Blueprint $table) {
            $table->string('building_id', 5)->primary(); // PK: รหัสอาคาร
            $table->string('room_number', 5)->nullable(); // รหัสห้อง
            $table->string('building_name', 255); // ชื่ออาคาร
            $table->string('department_name', 50)->nullable(); // ชื่อแผนก
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
