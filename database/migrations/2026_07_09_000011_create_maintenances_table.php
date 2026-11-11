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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->string('maintenance_id', 5)->primary(); // PK: รหัสบันทึกการซ่อม
            $table->string('repair_detail_id', 5); // FK: รหัสรายละเอียดการแจ้งซ่อม
            $table->string('user_id', 8); // FK: รหัสผู้ใช้
            $table->text('repair_action')->nullable(); // รายละเอียดสิ่งที่ได้ดำเนินการซ่อม
            $table->date('repair_start_date')->nullable(); // วันที่เริ่มดำเนิน
            $table->date('completion_date')->nullable(); // วันที่ซ่อมเสร็จ
            $table->string('repair_result', 50)->nullable(); // ผลการซ่อม (Enum/String)
            $table->timestamps();

            // Constraints
            $table->foreign('repair_detail_id')->references('repair_detail_id')->on('repair_details')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
