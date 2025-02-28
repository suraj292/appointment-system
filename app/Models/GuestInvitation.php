<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestInvitation extends Model
{
    protected $fillable = [
        'appointment_id',
        'email',
        'status',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
