<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sales_logs', 'designer_id')) {
            Schema::table('sales_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('designer_id')->nullable()->after('job_id');
            });
        }

        DB::table('staff')->whereNull('salary')->update(['salary' => 0]);
        DB::table('print_jobs')->whereNull('amount')->update(['amount' => 0]);

        DB::statement('ALTER TABLE staff MODIFY salary DECIMAL(12,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE print_jobs MODIFY amount DECIMAL(12,2) NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales_logs', 'designer_id')) {
            Schema::table('sales_logs', function (Blueprint $table) {
                $table->dropColumn('designer_id');
            });
        }
    }
};
