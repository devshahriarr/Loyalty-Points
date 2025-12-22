<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = ['code', 'name', 'price', 'is_active'];

    public function limits()
    {
        return $this->hasMany(SubscriptionLimit::class);
    }

    public function features()
    {
        return $this->hasMany(SubscriptionFeature::class);
    }
}
