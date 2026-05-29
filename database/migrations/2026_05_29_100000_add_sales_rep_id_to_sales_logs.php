<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sales_logs', 'sales_rep_id')) {
            Schema::table('sales_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('sales_rep_id')->nullable()->after('logged_by');
            });
        }

        $staff = DB::table('staff')->get(['id', 'name', 'user_id']);

        foreach ($staff as $member) {
            DB::table('sales_logs')
                ->whereNull('sales_rep_id')
                ->where('logged_by', $member->name)
                ->update(['sales_rep_id' => $member->id]);

            if ($member->user_id) {
                $userName = DB::table('users')->where('id', $member->user_id)->value('name');
                if ($userName) {
                    DB::table('sales_logs')
                        ->whereNull('sales_rep_id')
                        ->where('logged_by', $userName)
                        ->update(['sales_rep_id' => $member->id]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales_logs', 'sales_rep_id')) {
            Schema::table('sales_logs', function (Blueprint $table) {
                $table->dropColumn('sales_rep_id');
            });
        }
    }
};
