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
        Schema::create('users', function (Blueprint $table) {
            $table->string('user_id', 8)->primary(); // PK: รหัสผู้ใช้
            $table->string('name', 30); // ชื่อ
            $table->string('prefix', 30)->nullable(); // คำนำหน้า
            $table->string('first_name', 30)->nullable(); // ชื่อจริง (สำหรับ Laravel)
            $table->string('last_name', 30)->nullable(); // นามสกุล
            $table->string('gender', 10)->nullable(); // เพศ
            $table->string('email', 50)->unique(); // อีเมล
            $table->string('phone_number', 10)->nullable(); // เบอร์โทรศัพท์ (Data Dictionary)
            $table->string('phone', 20)->nullable(); // เบอร์โทรศัพท์ (สำหรับ Laravel)
            $table->string('position_id', 2)->nullable(); // FK: รหัสตำแหน่ง (Data Dictionary)
            $table->string('department_id', 2)->nullable(); // FK: รหัสแผนก (Data Dictionary)
            $table->string('department', 255)->nullable(); // แผนก (สำหรับ Laravel)
            $table->string('position', 255)->nullable(); // ตำแหน่ง (สำหรับ Laravel)
            
            // ฟิลด์เสริมเพื่อให้ระบบล็อกอินและสิทธิ์ของ Laravel ทำงานร่วมกันได้
            $table->string('password', 255)->nullable();
            $table->string('role', 50)->nullable();
            $table->string('status', 50)->default('approved');
            $table->string('profile_photo', 255)->nullable();
            $table->rememberToken();
            $table->timestamps();

            // Constraints
            $table->foreign('position_id')->references('position_id')->on('positions')->onDelete('set null');
            $table->foreign('department_id')->references('department_id')->on('departments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
