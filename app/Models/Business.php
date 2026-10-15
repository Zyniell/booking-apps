<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_usaha',
        'deskripsi_singkat',
        'alamat',
        'telepon',
        'email',
        'jenis_booking',
        'durasi_booking',
        'jam_buka',
        'jam_tutup',
        'logo',
        'is_booking_open',
        'catatan_booking',
    ];

    protected $casts = [
        'is_booking_open' => 'boolean',
        'jam_buka' => 'datetime:H:i',
        'jam_tutup' => 'datetime:H:i',
    ];

    public function scopeAktif($query)
    {
        return $query->where('is_booking_open', true);
    }

    public function getJenisBookingLabelAttribute()
    {
        return match ($this->jenis_booking) {
            'per_jam' => 'Per Jam',
            'per_12_jam' => 'Per 12 Jam',
            'per_hari' => 'Per Hari',
            'custom' => 'Custom',
            default => ucfirst($this->jenis_booking),
        };
    }
}
