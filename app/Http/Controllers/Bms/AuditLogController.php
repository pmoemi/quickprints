<?php

namespace App\Http\Controllers\Bms;

use App\Models\AuditLog;
use Illuminate\View\View;

class AuditLogController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('settings', 'read');
        $logs = AuditLog::query()->orderByDesc('created_at')->limit(500)->get();

        return view('audit.index', compact('logs'));
    }
}


