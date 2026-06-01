<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_logs', function (Blueprint $table) {
            $table->decimal('cash_amount', 12, 2)->default(0)->after('amount');
            $table->decimal('mpesa_amount', 12, 2)->default(0)->after('cash_amount');
        });

        // Backfill existing fully-paid records so the Cash/M-Pesa summaries are accurate
        DB::statement("UPDATE sales_logs SET cash_amount = amount WHERE pay_status = 'paid' AND pay_method = 'Cash'");
        DB::statement("UPDATE sales_logs SET mpesa_amount = amount WHERE pay_status = 'paid' AND LOWER(pay_method) IN ('mpesa', 'm-pesa')");
    }

    public function down(): void
    {
        Schema::table('sales_logs', function (Blueprint $table) {
            $table->dropColumn(['cash_amount', 'mpesa_amount']);
        });
    }
};
