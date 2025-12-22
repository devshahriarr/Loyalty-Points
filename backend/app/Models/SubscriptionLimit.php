<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionLimit extends Model
{

    protected $fillable = ['subscription_id', 'key', 'value'];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
