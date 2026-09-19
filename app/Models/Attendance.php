<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'member_id',
        'membership_id',
        'check_in_at',
        'method',
        'operator_id',
        'notes',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',

    ];

    protected $casts = [
        'check_in_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function cancelledByUser()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function isCancellable(): bool
    {
        if ($this->status === 'cancelled') {
            return false;
        }

        // Hardcode 1 hari (nanti pindah ke settings)
        return $this->check_in_at->gte(now()->subDay());
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}
