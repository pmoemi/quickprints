<?php

namespace App\Http\Controllers\Bms;

use Illuminate\View\View;

class MaintenanceController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('settings', 'read');

        return view('maintenance.index');
    }
}


