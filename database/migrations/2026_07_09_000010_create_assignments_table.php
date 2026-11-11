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
        Schema::create('assignments', function (Blueprint $table) {
            $table->string('assignment_id', 5)->primary(); // PK: รหัสการมอบหมายงาน
            $table->string('request_id', 5); // FK: รหัสการแจ้งซ่อม
            $table->string('user_id', 8); // FK: รหัสผู้ใช้ (ผู้รับมอบหมาย / ช่างซ่อม)
            $table->date('assign_date')->nullable(); // วันที่มอบหมายงาน
            $table->string('task_description', 255)->nullable(); // รายละเอียดงานที่สั่ง
            $table->string('task_priority', 50)->nullable(); // ระดับความสำคัญ (Enum/String)
            $table->timestamps();

            // Constraints
            $table->foreign('request_id')->references('request_id')->on('repair_requests')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
