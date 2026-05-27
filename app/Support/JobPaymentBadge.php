<?php

namespace App\Support;

use App\Models\PrintJob;

class JobPaymentBadge
{
    public static function invoiceMeta(PrintJob $job): string
    {
        return match ($job->paymentStatus()) {
            'full' => '<span class="badge badge-paid">FULLY PAID</span>',
            'partial' => '<span class="badge badge-partial">PARTIALLY PAID</span>',
            default => '<span class="badge badge-unpaid">PENDING</span>',
        };
    }
}
