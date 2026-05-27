<?php

use App\Http\Controllers\Bms\AssetController;
use App\Http\Controllers\Bms\AttendanceController;
use App\Http\Controllers\Bms\AuditLogController;
use App\Http\Controllers\Bms\Auth\ForgotPasswordController;
use App\Http\Controllers\Bms\Auth\LoginController;
use App\Http\Controllers\Bms\Auth\ResetPasswordController;
use App\Http\Controllers\Bms\BoardController;
use App\Http\Controllers\Bms\BranchFilterController;
use App\Http\Controllers\Bms\ClientController;
use App\Http\Controllers\Bms\CommissionController;
use App\Http\Controllers\Bms\DashboardController;
use App\Http\Controllers\Bms\InventoryController;
use App\Http\Controllers\Bms\JobController;
use App\Http\Controllers\Bms\KanbanController;
use App\Http\Controllers\Bms\LeadController;
use App\Http\Controllers\Bms\LeaveController;
use App\Http\Controllers\Bms\LedgerController;
use App\Http\Controllers\Bms\MaintenanceController;
use App\Http\Controllers\Bms\MessageController;
use App\Http\Controllers\Bms\NotificationController;
use App\Http\Controllers\Bms\OpexController;
use App\Http\Controllers\Bms\PayslipController;
use App\Http\Controllers\Bms\PayrollController;
use App\Http\Controllers\Bms\PettyCashController;
use App\Http\Controllers\Bms\PortalController;
use App\Http\Controllers\Bms\PortalPublicController;
use App\Http\Controllers\Bms\ProcurementController;
use App\Http\Controllers\Bms\PurchaseOrderController;
use App\Http\Controllers\Bms\QuoteController;
use App\Http\Controllers\Bms\ReconciliationController;
use App\Http\Controllers\Bms\RecurringBillController;
use App\Http\Controllers\Bms\SalesLogController;
use App\Http\Controllers\Bms\SalesTargetController;
use App\Http\Controllers\Bms\ServicesController;
use App\Http\Controllers\Bms\SettingsController;
use App\Http\Controllers\Bms\StaffController;
use App\Http\Controllers\Bms\SupplierController;
use App\Http\Controllers\Bms\ReportPdfController;
use App\Http\Controllers\Bms\VatReportController;
use Illuminate\Support\Facades\Route;

Route::get('portal/{token}', [PortalPublicController::class, 'show'])->name('portal.public');
Route::get('portal/{token}/invoice/{job}', [PortalPublicController::class, 'invoice'])->name('portal.public.invoice');
Route::get('portal/{token}/invoice/{job}/pdf', [PortalPublicController::class, 'invoicePdf'])->name('portal.public.invoice.pdf');
Route::get('portal/{token}/receipt/{job}/pdf', [PortalPublicController::class, 'receiptPdf'])->name('portal.public.receipt.pdf');

Route::name('bms.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'show'])->name('login');
        Route::post('login', [LoginController::class, 'store']);
        Route::get('forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
        Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
        Route::get('reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
        Route::post('reset-password', [ResetPasswordController::class, 'update'])->name('password.update');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        Route::post('branch', BranchFilterController::class)->name('branch');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard')->middleware('bms.page:dashboard');
        Route::get('kanban', [KanbanController::class, 'index'])->name('kanban')->middleware('bms.page:kanban');

        Route::resource('jobs', JobController::class)->middleware('bms.page:jobs');
        Route::get('jobs/{job}/invoice', [JobController::class, 'invoice'])->name('jobs.invoice')->middleware('bms.page:jobs');
        Route::get('jobs/{job}/invoice/pdf', [JobController::class, 'invoicePdf'])->name('jobs.invoice.pdf')->middleware('bms.page:jobs');
        Route::get('jobs/{job}/receipt/pdf', [JobController::class, 'receiptPdf'])->name('jobs.receipt.pdf')->middleware('bms.page:jobs');
        Route::post('jobs/{job}/invoice/send', [JobController::class, 'sendInvoice'])->name('jobs.invoice.send')->middleware('bms.page:jobs');
        Route::post('jobs/{job}/artwork', [JobController::class, 'uploadArtwork'])->name('jobs.artwork.upload')->middleware('bms.page:jobs');
        Route::delete('jobs/{job}/artwork', [JobController::class, 'removeArtwork'])->name('jobs.artwork.remove')->middleware('bms.page:jobs');
        Route::get('quotes/{quote}/pdf', [QuoteController::class, 'pdf'])->name('quotes.pdf')->middleware('bms.page:quotes');
        Route::get('reports/pdf/{type}', [ReportPdfController::class, 'download'])->name('reports.pdf')->middleware('bms.page:reports');
        Route::resource('clients', ClientController::class)->middleware('bms.page:clients');
        Route::resource('leads', LeadController::class)->except(['show'])->middleware('bms.page:leads');
        Route::resource('quotes', QuoteController::class)->except(['show'])->middleware('bms.page:quotes');
        Route::resource('inventory', InventoryController::class)->except(['show'])->middleware('bms.page:inventory');
        Route::resource('staff', StaffController::class)->except(['show'])->middleware('bms.page:staff');

        Route::get('saleslog', [SalesLogController::class, 'index'])->name('saleslog.index')->middleware('bms.page:saleslog');
        Route::get('saleslog/create', [SalesLogController::class, 'create'])->name('saleslog.create')->middleware('bms.page:saleslog');
        Route::post('saleslog', [SalesLogController::class, 'store'])->name('saleslog.store')->middleware('bms.page:saleslog');
        Route::delete('saleslog/{id}', [SalesLogController::class, 'destroy'])->name('saleslog.destroy')->middleware('bms.page:saleslog');

        Route::resource('opex', OpexController::class)->only(['index', 'create', 'store', 'destroy'])->middleware('bms.page:opex');
        Route::resource('payroll', PayrollController::class)->only(['index', 'create', 'store', 'destroy'])->middleware('bms.page:payroll');
        Route::resource('pettycash', PettyCashController::class)->only(['index', 'create', 'store', 'destroy'])->parameters(['pettycash' => 'pettycash'])->middleware('bms.page:petty-cash');
        Route::resource('assets', AssetController::class)->except(['show'])->middleware('bms.page:assets');
        Route::resource('procurement', ProcurementController::class)->only(['index', 'create', 'store', 'destroy'])->middleware('bms.page:procurement');

        Route::get('followups', [BoardController::class, 'followups'])->name('followups')->middleware('bms.page:followups');
        Route::get('designer', [BoardController::class, 'designer'])->name('designer')->middleware('bms.page:designer');
        Route::get('operator', [BoardController::class, 'operator'])->name('operator')->middleware('bms.page:operator');
        Route::get('fabrication', [BoardController::class, 'fabrication'])->name('fabrication')->middleware('bms.page:fabrication');
        Route::get('delivery', [BoardController::class, 'delivery'])->name('delivery')->middleware('bms.page:delivery');
        Route::get('payments', [BoardController::class, 'payments'])->name('payments')->middleware('bms.page:payments');
        Route::get('reports', [BoardController::class, 'reports'])->name('reports')->middleware('bms.page:reports');
        Route::patch('jobs/{job}/stage', [BoardController::class, 'advanceStage'])->name('jobs.stage');

        Route::get('messages', [MessageController::class, 'index'])->name('messages.index')->middleware('bms.page:messages');
        Route::get('messages/create', [MessageController::class, 'create'])->name('messages.create')->middleware('bms.page:messages');
        Route::post('messages', [MessageController::class, 'store'])->name('messages.store')->middleware('bms.page:messages');
        Route::get('messages/{id}', [MessageController::class, 'show'])->name('messages.show')->middleware('bms.page:messages');

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index')->middleware('bms.page:notifications');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all')->middleware('bms.page:notifications');
        Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read')->middleware('bms.page:notifications');

        Route::resource('suppliers', SupplierController::class)->except(['show'])->middleware('bms.page:suppliers');
        Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'create', 'store', 'destroy'])->middleware('bms.page:purchase-orders');
        Route::resource('recurring-bills', RecurringBillController::class)->only(['index', 'create', 'store', 'update', 'destroy'])->middleware('bms.page:recurring-bills');

        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index')->middleware('bms.page:attendance');
        Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store')->middleware('bms.page:attendance');
        Route::delete('attendance/{id}', [AttendanceController::class, 'destroy'])->name('attendance.destroy')->middleware('bms.page:attendance');

        Route::get('leave', [LeaveController::class, 'index'])->name('leave.index')->middleware('bms.page:leave');
        Route::get('leave/create', [LeaveController::class, 'create'])->name('leave.create')->middleware('bms.page:leave');
        Route::post('leave', [LeaveController::class, 'store'])->name('leave.store')->middleware('bms.page:leave');
        Route::post('leave/{id}/approve', [LeaveController::class, 'approve'])->name('leave.approve')->middleware('bms.page:leave');
        Route::post('leave/{id}/reject', [LeaveController::class, 'reject'])->name('leave.reject')->middleware('bms.page:leave');

        Route::get('payslips', [PayslipController::class, 'index'])->name('payslips.index')->middleware('bms.page:payslips');

        Route::resource('services', ServicesController::class)->except(['show'])->middleware('bms.page:services');

        Route::get('ledger', [LedgerController::class, 'index'])->name('ledger.index')->middleware('bms.page:ledger');
        Route::get('ledger/create', [LedgerController::class, 'create'])->name('ledger.create')->middleware('bms.page:ledger');
        Route::post('ledger', [LedgerController::class, 'store'])->name('ledger.store')->middleware('bms.page:ledger');

        Route::get('bank-recon', [ReconciliationController::class, 'bank'])->name('bank-recon')->middleware('bms.page:bank-recon');
        Route::post('bank-recon', [ReconciliationController::class, 'storeBankLine'])->name('bank-recon.store')->middleware('bms.page:bank-recon');
        Route::post('bank-recon/{id}/match', [ReconciliationController::class, 'matchBankLine'])->name('bank-recon.match')->middleware('bms.page:bank-recon');

        Route::get('cash-recon', [ReconciliationController::class, 'cash'])->name('cash-recon')->middleware('bms.page:cash-recon');

        Route::get('commissions', [CommissionController::class, 'index'])->name('commissions.index')->middleware('bms.page:commissions');
        Route::get('sales-targets', [SalesTargetController::class, 'index'])->name('sales-targets.index')->middleware('bms.page:sales-targets');
        Route::put('sales-targets', [SalesTargetController::class, 'update'])->name('sales-targets.update')->middleware('bms.page:sales-targets');

        Route::get('vat-report', [VatReportController::class, 'index'])->name('vat-report')->middleware('bms.page:vat-report');

        Route::get('audit', [AuditLogController::class, 'index'])->name('audit.index')->middleware('bms.page:audit-log');

        Route::get('portal', [PortalController::class, 'index'])->name('portal.index')->middleware('bms.page:portal');
        Route::post('portal', [PortalController::class, 'store'])->name('portal.store')->middleware('bms.page:portal');
        Route::delete('portal/{id}', [PortalController::class, 'destroy'])->name('portal.destroy')->middleware('bms.page:portal');

        Route::get('maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index')->middleware('bms.page:maintenance');
        Route::post('maintenance/clear-data', [MaintenanceController::class, 'clearData'])->name('maintenance.clear-data')->middleware('bms.page:maintenance');
        Route::post('maintenance/seed-demo', [MaintenanceController::class, 'seedDemo'])->name('maintenance.seed-demo')->middleware('bms.page:maintenance');
        Route::post('maintenance/seed-services', [MaintenanceController::class, 'seedServices'])->name('maintenance.seed-services')->middleware('bms.page:maintenance');
        Route::post('maintenance/reset-demo', [MaintenanceController::class, 'resetDemo'])->name('maintenance.reset-demo')->middleware('bms.page:maintenance');

        Route::post('settings/theme', function (\Illuminate\Http\Request $req) {
            $theme = $req->input('theme', 'dark') === 'light' ? 'light' : 'dark';
            $req->user()->update(['theme' => $theme]);
            return response()->json(['ok' => true]);
        })->name('settings.theme');

        Route::prefix('settings')->name('settings.')->middleware('bms.page:settings')->group(function () {
            Route::get('company', [SettingsController::class, 'company'])->name('company');
            Route::put('company', [SettingsController::class, 'updateCompany'])->name('company.update');
            Route::get('branches', [SettingsController::class, 'branches'])->name('branches');
            Route::post('branches', [SettingsController::class, 'storeBranch'])->name('branches.store');
            Route::put('branches/rename', [SettingsController::class, 'renameBranch'])->name('branches.rename');
            Route::delete('branches/{name}', [SettingsController::class, 'destroyBranch'])->name('branches.destroy')->where('name', '.*');
            Route::get('branding', [SettingsController::class, 'branding'])->name('branding');
            Route::put('branding', [SettingsController::class, 'updateBranding'])->name('branding.update');
            Route::post('branding/logo', [SettingsController::class, 'uploadLogo'])->name('branding.logo');
            Route::post('branding/favicon', [SettingsController::class, 'uploadFavicon'])->name('branding.favicon');
            Route::get('invoice', [SettingsController::class, 'invoice'])->name('invoice');
            Route::put('invoice', [SettingsController::class, 'updateInvoice'])->name('invoice.update');
            Route::post('invoice/preview', [SettingsController::class, 'previewInvoice'])->name('invoice.preview');
            Route::post('invoice/watermark-image', [SettingsController::class, 'uploadWatermarkImage'])->name('invoice.watermark-image');
            Route::get('invoice/design', [SettingsController::class, 'invoiceDesign'])->name('invoice.design');
            Route::get('email', [SettingsController::class, 'email'])->name('email');
            Route::put('email', [SettingsController::class, 'updateEmail'])->name('email.update');
            Route::post('email/test', [SettingsController::class, 'testEmail'])->name('email.test');
            Route::get('email-templates', [SettingsController::class, 'emailTemplates'])->name('email-templates');
            Route::put('email-templates', [SettingsController::class, 'updateEmailTemplate'])->name('email-templates.update');
            Route::post('email-templates/reset/{type}', [SettingsController::class, 'resetEmailTemplate'])->name('email-templates.reset')->where('type', '[a-z_]+');
            Route::get('finance', [SettingsController::class, 'finance'])->name('finance');
            Route::put('finance', [SettingsController::class, 'updateFinance'])->name('finance.update');
            Route::get('roles', [SettingsController::class, 'roles'])->name('roles');
            Route::post('roles', [SettingsController::class, 'storeRole'])->name('roles.store');
            Route::put('roles/{name}', [SettingsController::class, 'updateRole'])->name('roles.update')->where('name', '.*');
            Route::delete('roles/{name}', [SettingsController::class, 'destroyRole'])->name('roles.destroy')->where('name', '.*');
        });
    });
});
