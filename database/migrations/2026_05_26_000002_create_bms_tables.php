<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('company')->nullable();
            $table->string('branch')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('print_jobs', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('title');
            $table->string('branch')->nullable();
            $table->string('category')->nullable();
            $table->string('stage')->default('waiting');
            $table->string('priority')->default('medium');
            $table->decimal('amount', 12, 2)->default(0);
            $table->boolean('paid')->default(false);
            $table->unsignedBigInteger('designer_id')->nullable();
            $table->unsignedBigInteger('sales_rep_id')->nullable();
            $table->string('fab_substage')->nullable();
            $table->date('deadline')->nullable();
            $table->text('notes')->nullable();
            $table->json('history')->nullable();
            $table->json('follow_ups')->nullable();
            $table->timestamps();
        });

        Schema::create('staff', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->string('role');
            $table->string('branch')->default('all');
            $table->string('email')->nullable();
            $table->string('color')->nullable();
            $table->decimal('salary', 12, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sales_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->date('date')->nullable();
            $table->string('client_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('job_desc')->nullable();
            $table->string('category')->nullable();
            $table->string('branch')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('pay_status')->default('pending');
            $table->string('pay_method')->nullable();
            $table->string('logged_by')->nullable();
            $table->string('job_id')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->string('cat')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('qty', 12, 2)->default(0);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->string('branch')->nullable();
            $table->decimal('reorder_level', 12, 2)->default(0);
            $table->decimal('roll_width', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('quotes', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->date('date')->nullable();
            $table->string('client_name')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('branch')->nullable();
            $table->string('prepared_by')->nullable();
            $table->json('items')->nullable();
            $table->decimal('vat_rate', 8, 2)->default(16);
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('client_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('service')->nullable();
            $table->decimal('value', 12, 2)->default(0);
            $table->string('status')->default('new');
            $table->string('assigned_to')->nullable();
            $table->string('branch')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->timestamps();
        });

        Schema::create('opex_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->date('date')->nullable();
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('branch')->nullable();
            $table->string('paid_by')->nullable();
            $table->string('pay_method')->nullable();
            $table->timestamps();
        });

        Schema::create('payroll_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('month')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('staff_name')->nullable();
            $table->decimal('gross_salary', 12, 2)->default(0);
            $table->decimal('nhif', 12, 2)->default(0);
            $table->decimal('nssf', 12, 2)->default(0);
            $table->decimal('paye', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('petty_cash_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->date('date')->nullable();
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('branch')->nullable();
            $table->string('submitted_by')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->string('category')->nullable();
            $table->decimal('purchase_cost', 12, 2)->default(0);
            $table->decimal('current_value', 12, 2)->default(0);
            $table->string('condition_status')->nullable();
            $table->string('branch')->nullable();
            $table->timestamps();
        });

        Schema::create('procurement_entries', function (Blueprint $table) {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_entries');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('petty_cash_entries');
        Schema::dropIfExists('payroll_entries');
        Schema::dropIfExists('opex_entries');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('sales_logs');
        Schema::dropIfExists('staff');
        Schema::dropIfExists('print_jobs');
        Schema::dropIfExists('clients');
    }
};
