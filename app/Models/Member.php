<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_code',
        'type',
        'name',
        'whatsapp',
        'photo',
        'gender',
        'birth_date',
        'address',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'birth_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function activeMembership()
    {
        return $this->hasOne(Membership::class)
            ->where('status', 'active')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->latestOfMany();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Helper label tipe
    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'pelajar' ? 'Pelajar' : 'Non Pelajar';
    }

    // Generate member code otomatis
    public static function generateMemberCode(string $type): string
    {
        $prefix = $type === 'pelajar' ? 'EP' : 'EM';

        $lastMember = self::where('member_code', 'like', $prefix . '-%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastMember) {
            $lastNumber = (int) substr($lastMember->member_code, 3);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
