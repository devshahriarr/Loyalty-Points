<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CustomerPasswordOtp extends Model
{
    use UsesTenantConnection;

    protected $connection = "tenant";

    protected $fillable = [
        'email', 'otp', 'expires_at', 'verified'
    ];
}
