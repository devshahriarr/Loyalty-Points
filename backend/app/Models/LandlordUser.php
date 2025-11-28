<?php

namespace App\Models;

class LandlordUser extends User
{
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
