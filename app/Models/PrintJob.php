<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintJob extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'print_jobs';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'paid' => 'boolean',
            'amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'deadline' => 'date',
            'history' => 'array',
            'follow_ups' => 'array',
        ];
    }

    public function amountPaid(): float
    {
        return (float) ($this->amount_paid ?? 0);
    }

    public function balanceDue(): float
    {
        return max(0, (float) $this->amount - $this->amountPaid());
    }

    /** @return 'pending'|'partial'|'full' */
    public function paymentStatus(): string
    {
        $total = (float) $this->amount;
        $paid = $this->amountPaid();

        if ($total <= 0) {
            return $paid > 0 ? 'full' : 'pending';
        }

        if ($paid <= 0) {
            return 'pending';
        }

        if ($paid >= $total) {
            return 'full';
        }

        return 'partial';
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->paymentStatus()) {
            'full' => 'Fully paid',
            'partial' => 'Partially paid',
            default => 'Pending',
        };
    }

    protected static function booted(): void
    {
        static::saving(function (PrintJob $job) {
            if ($job->isDirty(['amount_paid', 'amount'])) {
                $total = (float) $job->amount;
                $paid = (float) ($job->amount_paid ?? 0);
                $job->paid = $total <= 0 ? $paid > 0 : $paid >= $total;
            }
        });
    }
}
