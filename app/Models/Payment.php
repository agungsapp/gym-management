<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'payment_number',
        'member_id',
        'membership_id',
        'original_price',
        'discount_type',
        'discount_value',
        'amount',
        'payment_method',
        'payment_date',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'original_price' => 'integer',
        'discount_value' => 'integer',
        'amount' => 'integer',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public static function generatePaymentNumber(): string
    {
        $prefix = 'PAY-' . now()->format('Ymd');
        $last = self::where('payment_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $next = $last
            ? ((int) substr($last->payment_number, -4)) + 1
            : 1;

        return $prefix . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
