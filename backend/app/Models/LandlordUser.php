<?php

namespace App\Models;

use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;
use Spatie\Permission\Traits\HasRoles;

class LandlordUser extends User
{
    use HasRoles, UsesLandlordConnection;
    protected $table = 'users';
    protected $connection = 'landlord';
    protected $guard_name = 'api';

    public function getConnectionName()
    {
        return 'landlord';
    }

}
