<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->string('contact')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('category')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->date('date')->nullable();
            $table->string('supplier')->nullable();
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('branch')->nullable();
            $table->string('status')->default('pending');
            $table->string('requested_by')->nullable();
            $table->timestamps();
        });

        Schema::create('recurring_bills', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('branch')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedTinyInteger('due_day')->default(1);
            $table->string('vendor')->nullable();
            $table->date('last_paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('staff_id');
            $table->string('staff_name')->nullable();
            $table->date('date');
            $table->string('check_in')->nullable();
            $table->string('check_out')->nullable();
            $table->decimal('hours', 8, 2)->default(0);
            $table->string('status')->default('Present');
            $table->string('source')->default('Manual');
            $table->timestamps();
            $table->unique(['staff_id', 'date']);
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('staff_id');
            $table->string('staff_name')->nullable();
            $table->string('leave_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();
            $table->string('status')->default('pending');
            $table->date('requested_date')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('rejected_by')->nullable();
            $table->date('approved_date')->nullable();
            $table->date('rejected_date')->nullable();
            $table->timestamps();
        });

        Schema::create('bms_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_name')->nullable();
            $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('to_role')->nullable();
            $table->string('subject');
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('bms_notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('type')->default('info');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('entry_id')->unique();
            $table->date('date')->nullable();
            $table->string('ref')->nullable();
            $table->string('description')->nullable();
            $table->string('posted_by')->nullable();
            $table->json('lines');
            $table->timestamps();
        });

        Schema::create('bank_statement_lines', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->date('date')->nullable();
            $table->string('description')->nullable();
            $table->string('type');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('bank')->nullable();
            $table->boolean('matched')->default(false);
            $table->string('matched_ref')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('role')->nullable();
            $table->string('branch')->nullable();
            $table->string('action');
            $table->timestamps();
        });

        Schema::create('portal_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('token', 64)->unique();
            $table->string('access_level')->default('basic');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_tokens');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('bank_statement_lines');
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('bms_notifications');
        Schema::dropIfExists('bms_messages');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('recurring_bills');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
    }
};
