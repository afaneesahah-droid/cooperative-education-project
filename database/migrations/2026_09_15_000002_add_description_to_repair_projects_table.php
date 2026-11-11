<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repair_projects', function (Blueprint $table) {
            if (!Schema::hasColumn('repair_projects', 'description')) {
                $table->text('description')->nullable()->after('budget_source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('repair_projects', function (Blueprint $table) {
            if (Schema::hasColumn('repair_projects', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
