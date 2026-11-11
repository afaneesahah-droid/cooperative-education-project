<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repair_projects', function (Blueprint $table) {
            if (!Schema::hasColumn('repair_projects', 'budget_source')) {
                $table->string('budget_source')->default('งบประมาณกลาง')->after('fiscal_year')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('repair_projects', function (Blueprint $table) {
            if (Schema::hasColumn('repair_projects', 'budget_source')) {
                $table->dropColumn('budget_source');
            }
        });
    }
};
