<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MeetingRoom extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'capacity'];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
