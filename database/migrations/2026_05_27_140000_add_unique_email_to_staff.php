<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Deduplicate any existing staff rows with duplicate emails before adding constraint
        \DB::statement("
            DELETE s1 FROM staff s1
            INNER JOIN staff s2
            WHERE s1.id > s2.id AND s1.email = s2.email AND s1.email IS NOT NULL
        ");

        Schema::table('staff', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->string('email')->nullable()->change();
        });
    }
};
