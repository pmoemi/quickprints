<?php

namespace App\Http\Controllers\Bms;

use App\Models\Client;
use App\Models\PrintJob;
use Illuminate\View\View;

class KanbanController extends BmsController
{
    public function index(): View
    {
        $this->authorizeBms('jobs', 'read');

        $stages = ['waiting', 'designing', 'approval', 'printing', 'fabrication', 'ready', 'installed', 'paid'];
        $jobs = $this->scopeBranch(PrintJob::query())->get()->groupBy('stage');
        $clients = Client::query()->get()->keyBy('id');

        return view('kanban', compact('stages', 'jobs', 'clients'));
    }
}


