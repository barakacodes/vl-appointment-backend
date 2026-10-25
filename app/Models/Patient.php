<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'age',
        'gender',
        'email',
        'distance_km',
        'previous_missed_count',
        'avg_delay_days',
        'next_appointment_date',
        'risk_score',
        'risk_tier',
        'patient_confirmed_at',
    ];

    protected $casts = [
        'patient_confirmed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
