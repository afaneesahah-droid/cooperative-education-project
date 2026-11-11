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
        Schema::create('repair_items', function (Blueprint $table) {
            $table->string('repair_item_id', 5)->primary(); // PK: รหัสรายการอุปกรณ์แจ้งซ่อม
            $table->string('request_id', 5); // FK: รหัสการแจ้งซ่อม
            $table->string('equipment_id', 5); // FK: รหัสอุปกรณ์
            $table->text('problem_description')->nullable(); // อาการเสีย/ปัญหาที่พบ
            $table->timestamps();

            // Constraints
            $table->foreign('request_id')->references('request_id')->on('repair_requests')->onDelete('cascade');
            $table->foreign('equipment_id')->references('equipment_id')->on('repair_equipments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_items');
    }
};
