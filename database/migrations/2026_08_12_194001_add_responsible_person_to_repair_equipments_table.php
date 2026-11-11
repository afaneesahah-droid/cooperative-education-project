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
        Schema::table('repair_equipments', function (Blueprint $table) {
            if (!Schema::hasColumn('repair_equipments', 'responsible_person')) {
                $table->string('responsible_person')->nullable()->after('room_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repair_equipments', function (Blueprint $table) {
            //
        });
    }
};
