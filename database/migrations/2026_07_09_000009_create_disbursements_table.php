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
        Schema::create('disbursements', function (Blueprint $table) {
            $table->string('disbursement_id', 5)->primary(); // PK: รหัสการเบิก
            $table->string('request_id', 5); // FK: รหัสการแจ้งซ่อม
            $table->string('user_id', 8); // FK: รหัสผู้ใช้
            $table->string('approver_id', 8)->nullable(); // FK: รหัสผู้อนุมัติ
            $table->date('disbursement_date')->nullable(); // วันที่ขอเบิก
            $table->decimal('total_amount', 10, 2)->nullable(); // ยอดรวมค่าใช้จ่าย
            $table->string('approval_status', 50)->nullable(); // สถานะการอนุมัติ (Enum/String)
            $table->timestamps();

            // Constraints
            $table->foreign('request_id')->references('request_id')->on('repair_requests')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('approver_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disbursements');
    }
};
