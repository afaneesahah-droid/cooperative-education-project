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
        Schema::create('repair_equipments', function (Blueprint $table) {
            $table->string('equipment_id', 5)->primary(); // PK: รหัสอุปกรณ์ (Data Dictionary)
            $table->string('equipment_name', 40); // ชื่ออุปกรณ์ (Data Dictionary)
            $table->string('image', 255)->nullable(); // รูปภาพ (Data Dictionary)
            
            // ฟิลด์เสริมเพื่อรองรับระบบแจ้งซ่อมเดิมของ Laravel
            $table->string('device_code', 50)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('serial', 100)->nullable();
            $table->string('type', 100)->nullable();
            $table->string('spec', 255)->nullable();
            $table->string('status', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_equipments');
    }
};
