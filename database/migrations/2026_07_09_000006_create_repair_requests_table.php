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
        Schema::create('repair_requests', function (Blueprint $table) {
            $table->string('request_id', 5)->primary(); // PK: รหัสการแจ้งซ่อม (Data Dictionary)
            $table->string('user_id', 8); // FK: รหัสผู้ใช้ (Data Dictionary)
            $table->string('equipment_id', 5)->nullable(); // FK: รหัสอุปกรณ์แจ้งซ่อม (Data Dictionary)
            $table->date('request_date')->nullable(); // วันที่แจ้งซ่อม (Data Dictionary)
            $table->string('building', 80)->nullable(); // อาคาร (Data Dictionary)
            $table->string('equipment_number', 2)->nullable(); // หมายเลขอุปกรณ์ (Data Dictionary)
            $table->string('room_number', 3)->nullable(); // รหัสห้อง (Data Dictionary)
            $table->string('request_description', 255)->nullable(); // รายละเอียดการแจ้งซ่อม (Data Dictionary)
            
            // ฟิลด์เสริมเพื่อรองรับระบบแจ้งซ่อมเดิมของ Laravel
            $table->string('request_code', 50)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('type', 100)->nullable();
            $table->text('description')->nullable();
            $table->decimal('budget', 10, 2)->nullable();
            $table->string('status', 50)->nullable();
            $table->string('assigned_to', 8)->nullable();
            $table->string('priority', 50)->nullable();
            $table->text('note')->nullable();
            $table->boolean('budget_approved')->default(false);
            $table->integer('rating')->default(0);
            $table->boolean('checked_power')->default(false);
            $table->boolean('checked_cleaning')->default(false);
            $table->boolean('checked_ping')->default(false);
            $table->boolean('repair_hw')->default(false);
            $table->boolean('repair_sw')->default(false);
            $table->boolean('repair_net')->default(false);
            $table->timestamps();

            // Constraints
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('equipment_id')->references('equipment_id')->on('repair_equipments')->onDelete('set null');
            $table->foreign('assigned_to')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_requests');
    }
};
