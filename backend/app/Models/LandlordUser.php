<?php

namespace App\Models;

use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class LandlordUser extends User
{
    use UsesLandlordConnection;
    protected $table = 'users';
    protected $connection = 'landlord';
    protected $guard_name = 'api';

    public function getConnectionName()
    {
        return 'landlord';
    }

    public function businesses()
    {
        return $this->hasMany(Business::class);
    }
}
