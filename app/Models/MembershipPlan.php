<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipPlan extends Model
{
    protected $fillable = [
        'name',
        'duration',
        'duration_unit',
        'price',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'integer',
        'duration' => 'integer',
    ];

    // Helper untuk menampilkan durasi yang rapi
    public function getDurationLabelAttribute(): string
    {
        return $this->duration . ' ' . ($this->duration_unit === 'days' ? 'hari' : 'bulan');
    }

    // Format harga
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }
}
