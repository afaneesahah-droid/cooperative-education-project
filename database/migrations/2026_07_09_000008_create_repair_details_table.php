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
        Schema::create('repair_details', function (Blueprint $table) {
            $table->string('repair_detail_id', 5)->primary(); // PK: รหัสรายละเอียดการแจ้งซ่อม
            $table->string('approval_id', 5)->nullable(); // FK: รหัสการแจ้งซ่อม (Approval ID)
            $table->string('equipment_id', 5)->nullable(); // FK: รหัสอุปกรณ์
            $table->string('building_id', 5)->nullable(); // FK: รหัสอาคาร
            $table->string('room_number', 3)->nullable(); // รหัสห้อง
            $table->date('repair_start_date')->nullable(); // วันที่ดำเนินการซ่อม
            $table->date('completion_date')->nullable(); // วันที่ซ่อมเสร็จ
            $table->string('repair_status', 50)->nullable(); // สถานะการซ่อม (Enum/String)
            $table->string('approval_status', 50)->nullable(); // สถานะการอนุมัติ (Enum/String)
            $table->date('approval_date')->nullable(); // วันที่อนุมัติ
            $table->timestamps();

            // Constraints
            $table->foreign('approval_id')->references('request_id')->on('repair_requests')->onDelete('set null');
            $table->foreign('equipment_id')->references('equipment_id')->on('repair_equipments')->onDelete('set null');
            $table->foreign('building_id')->references('building_id')->on('buildings')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_details');
    }
};
