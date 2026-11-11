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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->string('withdrawal_id', 5)->primary(); // PK: รหัสการเบิกจ่าย
            $table->string('request_id', 5); // FK: รหัสการแจ้งซ่อม
            $table->string('user_id', 8); // FK: รหัสผู้ใช้
            $table->string('equipment_id', 5); // FK: รหัสอุปกรณ์ที่ขอเบิก
            $table->date('disbursement_date')->nullable(); // วันที่ขอเบิก
            $table->string('withdrawal_reason', 255)->nullable(); // หมายเหตุ / เหตุผลการเบิก
            $table->timestamps();

            // Constraints
            $table->foreign('request_id')->references('request_id')->on('repair_requests')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('equipment_id')->references('equipment_id')->on('repair_equipments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
