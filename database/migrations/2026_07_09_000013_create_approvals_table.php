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
        Schema::create('approvals', function (Blueprint $table) {
            $table->string('approver_id', 8)->primary(); // PK: รหัสผู้ใช้ / รหัสการอนุมัติ (references users.user_id)
            $table->string('approval_status', 50)->nullable(); // สถานะการอนุมัติ (Enum/String)
            $table->date('approver_date')->nullable(); // วันที่อนุมัติ
            $table->timestamps();

            // Constraints
            $table->foreign('approver_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
