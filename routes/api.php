<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BmsResourceController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\StaffAccountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);
Route::get('/settings/branding', [SettingsController::class, 'branding']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/settings', [SettingsController::class, 'show']);
    Route::get('/auth/permissions', [SettingsController::class, 'permissions']);
    Route::put('/settings', [SettingsController::class, 'update']);
    Route::post('/settings/branches', [SettingsController::class, 'addBranch']);
    Route::put('/settings/branches/rename', [SettingsController::class, 'renameBranch']);
    Route::delete('/settings/branches/{name}', [SettingsController::class, 'deleteBranch'])->where('name', '.*');
    Route::get('/settings/branches/{name}/usage', [SettingsController::class, 'branchUsage'])->where('name', '.*');
    Route::post('/settings/logo', [SettingsController::class, 'uploadLogo']);
    Route::post('/settings/favicon', [SettingsController::class, 'uploadFavicon']);

    $resources = [
        'jobs',
        'clients',
        'staff',
        'saleslog',
        'quotes',
        'leads',
        'inventory',
    ];

    foreach ($resources as $resource) {
        Route::get("/{$resource}", fn (Request $request, BmsResourceController $controller) => $controller->index($request, $resource));
        Route::post("/{$resource}", fn (Request $request, BmsResourceController $controller) => $controller->store($request, $resource));
        Route::put("/{$resource}/{id}", fn (Request $request, BmsResourceController $controller, string $id) => $controller->update($request, $resource, $id));
        Route::delete("/{$resource}/{id}", fn (Request $request, BmsResourceController $controller, string $id) => $controller->destroy($request, $resource, $id));
    }

    $financeResources = [
        'opex' => 'opex',
        'payroll' => 'payroll',
        'pettycash' => 'pettycash',
        'assets' => 'assets',
        'procurement' => 'procurement',
    ];

    foreach ($financeResources as $path => $resource) {
        Route::get("/finance/{$path}", fn (Request $request, BmsResourceController $controller) => $controller->index($request, $resource));
        Route::post("/finance/{$path}", fn (Request $request, BmsResourceController $controller) => $controller->store($request, $resource));
        Route::put("/finance/{$path}/{id}", fn (Request $request, BmsResourceController $controller, string $id) => $controller->update($request, $resource, $id));
        Route::delete("/finance/{$path}/{id}", fn (Request $request, BmsResourceController $controller, string $id) => $controller->destroy($request, $resource, $id));
    }

    Route::post('/staff/{id}/account', [StaffAccountController::class, 'store']);
});
