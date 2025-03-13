<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'subscribed_at',
        'expires_at'
    ];

    protected $dates = ['subscribed_at', 'expires_at'];

    public function isExpired()
    {
        return $this->expires_at && Carbon::now()->gt($this->expires_at);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

}
