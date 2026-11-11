<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * M02 – Normalize positions table
 * 3NF Fix: ลบ `role` ออกจาก positions (role กำหนดที่ users.role เท่านั้น)
 * Transitive dependency: role ไม่ได้ขึ้นอยู่กับ position_id โดยตรง
 * เพราะผู้ใช้คนเดียวกัน (position เดียวกัน) สามารถมี role ต่างกันได้
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->string('role', 50)->nullable()->after('position_name');
        });
    }
};
