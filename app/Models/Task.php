<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
    ];

    public function taskUser()
    {
        return $this->hasMany(User::class);
    }

}
