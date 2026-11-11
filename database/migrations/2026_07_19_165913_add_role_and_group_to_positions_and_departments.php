<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->string('role', 50)->nullable()->after('position_name');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->string('group', 50)->nullable()->after('department_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }
};
