<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Booking extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'item_id',
        'booking_code',
        'customer_name',
        'customer_phone',
        'customer_email',
        'booking_date',
        'starts_at',
        'ends_at',
        'slot_duration',
        'slot_count',
        'base_price',
        'total_amount',
        'status',
        'payment_status',
        'payment_reference',
        'meta',
        'customer_note',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'meta' => 'array',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function getDurationInMinutesAttribute(): int
    {
        return $this->slot_duration * $this->slot_count;
    }

    public function scopeOverlap($query, Carbon $start, Carbon $end)
    {
        return $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('starts_at', [$start, $end])
                ->orWhereBetween('ends_at', [$start, $end])
                ->orWhere(function ($inner) use ($start, $end) {
                    $inner->where('starts_at', '<=', $start)
                        ->where('ends_at', '>=', $end);
                });
        });
    }
}
