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
        if (!Schema::hasColumn('repair_requests', 'eval_comment')) {
            Schema::table('repair_requests', function (Blueprint $table) {
                $table->text('eval_comment')->nullable()->after('rating');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('repair_requests', 'eval_comment')) {
            Schema::table('repair_requests', function (Blueprint $table) {
                $table->dropColumn('eval_comment');
            });
        }
    }
};
