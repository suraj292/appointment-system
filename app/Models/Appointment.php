<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'date_time',
        'timezone',
        'status',
    ];

    public function emails()
    {
        return $this->hasMany(Email::class);
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }

    public function guestInvitations()
    {
        return $this->hasMany(GuestInvitation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending()
    {
        return $this->where('status', 'Scheduled');
    }
}
