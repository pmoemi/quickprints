<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bms_notifications', function (Blueprint $table) {
            $table->string('job_id')->nullable()->after('user_id');
            $table->foreignId('related_user_id')->nullable()->after('job_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bms_notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('related_user_id');
            $table->dropColumn('job_id');
        });
    }
};
