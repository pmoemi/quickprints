<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->decimal('amount_paid', 12, 2)->default(0)->after('amount');
        });

        DB::table('print_jobs')->where('paid', true)->update([
            'amount_paid' => DB::raw('amount'),
        ]);
    }

    public function down(): void
    {
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->dropColumn('amount_paid');
        });
    }
};
